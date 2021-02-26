<?php
namespace BlazeBase\States;

use BlazeBase\Cards\BattingCards;
use BlazeBase\Game\Log;
use BlazeBase\Cards\Cards;
use BlazeBase\Cards\TrophyCards;
use BlazeBase\Game\Notifications;
use BlazeBase\Players\Players;
use Blaze;

trait TurnTrait
{
    public function stStartOfRound() {
        // 플레이어 기본 정보 불러오기
        $players = Blaze::get()->loadPlayersBasicInfos();
        $current_round = Blaze::get()->getGameStateValue('round');

        if ($current_round == 1) {
            // 플레이어 당 5장의 카드 배분
            foreach ($players as $player_id => $player) {
                Cards::draw(5, $player_id);
            }

            // 카드 배분 후 반으로 나누어 임시 덱에 넣은 후 1라운드 진행
            $current_deck_count = Cards::getDeckCount();
            Cards::moveToTemplateDeck(round($current_deck_count / 2));

        } else {
            Cards::roundStart($players);
        }

        // 트로피 카드 설정
        $player_count = self::getPlayersNumber() - 1;
        Blaze::get()->setGameStateValue("trophyCardId", $player_count * $current_round);

        // 카드 맨 위에 있는 카드를 트럼프 슈트로 등록
        $trump_suit_card =  Cards::drawTrumpSuitCard();
        Blaze::get()->setGameStateValue("trumpSuitType", $trump_suit_card->getType());
        Blaze::get()->setGameStateValue("trumpSuitValue", $trump_suit_card->getValue());

        // 업데이트 해준다
        Notifications::roundStart($current_round);

        // stStartOfMainTurn
        $this->gamestate->nextState("");
    }

    public function stEndOfRound() {
        $current_round = Blaze::get()->getGameStateValue('round');
        if ($current_round >= 2) {
            // gameEnd
            Notifications::roundStart(2);
            $this->gamestate->nextState("end");
            return;
        }        
        
        // 마지막 플레이어 불러오기
        $last_player_id = 0;
        $players = Players::getPlayers();
        foreach ($players as $player) {
            if ($player->isEliminated() == false) {
                $last_player_id = $player->getId();
            }
        }

        // 예외된 플레이어 다시 설정
        foreach ($players as $player) {
            $player->eliminate(false);
        }

        // 마지막으로 예외되지 않는 플레이어가 꼴등이므로 배팅 된 카드 확인 후 0 이면 돌려주고 아니면 점수로 등록
        $betting_card = BattingCards::getBettingCards();

        foreach ($betting_card as $card) {
            // 0의 카드는 항상 다시 자기 위치로
            if ($card['value'] == 0) {
                foreach ($players as $player) {
                    if ($player->getNo() == ((int)$card['type'] + 1)) {
                        BattingCards::moveCard($card['id'], 'hand', $player->getId());
                    }
                }
            } else {
                // 배팅된 카드가 맞으면 플레이어에게 돌려준다.
                if ($card['location_arg'] == $last_player_id) {
                    foreach ($players as $player) {
                        if ($player->getNo() == ($card['type'] + 1)) {
                            BattingCards::moveCard($card['id'], 'betted', $player->getId());
                        }
                    }
                } else {
                    BattingCards::moveCard($card['id'], 'betted', $card['location_arg']);
                }
            }
        }

        // 라운드가 끝나면 2라운드로 설정
        Blaze::get()->setGameStateValue("round", 2);

        // 배팅 여부 초기화
        Blaze::get()->setGameStateValue("isBetting", 0 );
        
        // stStartOfRound
        $this->gamestate->nextState("start");
    }

    public function stStartOfMainTurn() {
        $players = Players::getPlayers();

        $eliminated_player_count = 0;
        foreach ($players as $player) {
            if ($player->isEliminated() == false) {
                $eliminated_player_count++;
            }
        }
        
        // 플레이어가 혼자 남으면 라운드 종료
        if ($eliminated_player_count <= 1) {
            // stEndOfRound
            $this->gamestate->nextState("end");
            return;
        }


        // 공격자 및 수비자, 지원자 지정
        // 공격자 = 1, 수비자 = 2, 지원자 = 3
        $player = Players::getActivePlayer();
        Players::updatePlayersRole($player->getId());

        // 순서 및 수비 성공 여부 초기화
        Blaze::get()->setGameStateValue("nextOrder", 0);
        Blaze::get()->setGameStateValue('isAttacked', 0 );
        Blaze::get()->setGameStateValue('isDefensed', 0 );

        // 수비자 카드 수에 따른 공격 카드 제한
        $limitCount = is_null(Players::getPlayerWithRole(DEFENDER)['hand']) ? 0 : Players::getPlayerWithRole(DEFENDER)['hand'];
        Blaze::get()->setGameStateValue('limitCount', $limitCount );

        // stStartOfSubTurn
        $this->gamestate->nextState("start");
    }

    public function stEndOfMainTurn() {
        $players = Players::getPlayers();
        $deckCount = Cards::getDeckCount();
        $is_betting = Blaze::get()->getGameStateValue("isBetting");
        $is_defensed = Blaze::get()->getGameStateValue("isDefensed");
        Blaze::get()->setGameStateValue('isDefensed', 0 );      // 초기화

        // 수비 성공시 수비자 -> 공격자
        // 수비 실패시 지원자 -> 공격자
        $role = $is_defensed == DEFENSE_SUCCESS ? DEFENDER : VOLUNTEER;
        foreach ($players as $player) {
            if ($player->getRole() == $role) {
                $this->gamestate->changeActivePlayer($player->getId());
                break;
            }
        }

        // 역할 초기화
        foreach ($players as $player) {
            $player->updateRole(0);
        }
        
        if ($deckCount <= 0 && $is_betting == 0) {
            // stBatting
            $this->gamestate->nextState("end");
            return;
        }
        

        // stStartOfMainTurn
        $this->gamestate->nextState("start");
    }

    public function stStartOfSubTurn() {        
        $player = Players::getActivePlayer();
        $is_betting = Blaze::get()->getGameStateValue("isBetting");
        $is_defensed = Blaze::get()->getGameStateValue("isDefensed");

        $active_player_card_count = Cards::countCards('hand', $player->getId());
        if ($active_player_card_count <= 0 && $is_betting == 1) {
            if ($player->getRole() == ATTACKER) {
                $attack_cards = Cards::getAttackCards();
                $defense_cards = Cards::getDefenseCards();
                if ($is_defensed == DEFENSE_FAILURE) {
                    // stPlayerTurn
                    $this->gamestate->nextState("start");
                    return;
                } else {
                    Cards::discardAttackAndDefenseCards();
                    Notifications::defeneseSuccess($player, $attack_cards, $defense_cards);
                }
            }
            // stStartOfMainTurn
            $this->gamestate->nextState("end");
            return;
        }

        // stPlayerTurn
        $this->gamestate->nextState("start");
    }

    public function stEndOfSubTurn() {
        $is_defensed = Blaze::get()->getGameStateValue("isDefensed");
        $active_player = Players::getPlayer(self::getActivePlayerId());

        // 활성화된 플레이어의 카드가 없으면 트로피 카드 제공과 제외시킨다.
        // 활성화된 플레이어가 한명뿐이라면 트로피 카드 제공하지 않는다.
        
        $players = Players::getPlayers();
        $eliminated_player_count = 0;
        foreach ($players as $player) {
            if ($player->isEliminated() == false) {
                $eliminated_player_count++;
            }
        }
        
        $deckCount = Cards::getDeckCount();
        $is_betting = Blaze::get()->getGameStateValue("isBetting");
        $count = Cards::countCards('hand', $active_player->getId());
        
        if ($deckCount <= 0 && 
            $count <= 0 &&
            $is_betting == 1 &&
            $eliminated_player_count >= 2 &&            // 남아 있는 플레이어가 2명 이상일 때 지급
            $active_player->isEliminated() == false) 
        {
            $trophy_card_id = Blaze::get()->getGameStateValue('trophyCardId');
            $active_player->eliminate(true);
            TrophyCards::moveCard($trophy_card_id, "hand", $active_player->getId());
            Notifications::getTrophyCard($active_player, $trophy_card_id);
            Blaze::get()->setGameStateValue('trophyCardId', $trophy_card_id - 1);
        }

        $players = Players::getPlayers();
        $eliminated_player_count = 0;
        foreach ($players as $player) {
            if ($player->isEliminated() == false) {
                $eliminated_player_count++;
            }
        }

        // 방어를 성공하거나 플레이어가 1명밖에 남지 않았다면 넘어간다.
        if ($is_defensed == DEFENSE_SUCCESS || $eliminated_player_count == 1 || 
        ($eliminated_player_count == 2 && $is_defensed == DEFENSE_FAILURE) ||
        ($is_defensed == DEFENSE_FAILURE  && $active_player->getRole() == DEFENDER)) {
            // ST_DRAW_CARD
            $this->gamestate->nextState("end");
            return;
        }

        // stStartOfSubTurn
        $this->gamestate->nextState("start");
    }
}
