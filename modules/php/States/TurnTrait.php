<?php
namespace Blaze\States;

use Blaze\Cards\BattingCards;
use Blaze\Game\Log;
use Blaze\Cards\Cards;
use Blaze\Cards\TrophyCards;
use Blaze\Game\Notifications;
use Blaze\Players\Players;
use BlazeBananani;

trait TurnTrait
{
    public function stStartOfRound() {
        // 플레이어 기본 정보 불러오기
        $players = BlazeBananani::get()->loadPlayersBasicInfos();
        $current_round = BlazeBananani::get()->getGameStateValue('round');

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
        BlazeBananani::get()->setGameStateValue("trophyCardId", $player_count * $current_round);

        // 카드 맨 위에 있는 카드를 트럼프 슈트로 등록
        $trump_suit_card =  Cards::drawTrumpSuitCard();
        BlazeBananani::get()->setGameStateValue("trumpSuit", $trump_suit_card->getType());

        // 업데이트 해준다
        Notifications::roundStart($current_round);

        // stStartOfMainTurn
        $this->gamestate->nextState("");
    }

    public function stEndOfRound() {
        $current_round = BlazeBananani::get()->getGameStateValue('round');
        if ($current_round >= 2) {
            // gameEnd
            $this->gamestate->nextState("end");
            return;
        }        
        
        // 마지막 플레이어 불러오기
        $last_player_id = 0;
        // 예외된 플레이어 다시 설정
        $players = Players::getPlayers();
        foreach ($players as $player) {
            if ($player->isEliminated() == true) {
                $last_player_id = $player->getId();
            }
            $player->eliminate(false);
        }

        // 마지막으로 예외되지 않는 플레이어가 꼴등이므로 배팅 된 카드 확인 후 0 이면 돌려주고 아니면 점수로 등록
        $betting_card = BattingCards::getBettingCards();

        foreach ($betting_card as $card) {
            // 0의 카드는 항상 다시 자기 위치로
            if ($card['value'] == 0) {
                foreach ($players as $player) {
                    if ($player->getNo() == ($card['type'] + 1)) {
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
                    BattingCards::moveCard($card['id'], 'betted', $last_player_id);
                }
            }
        }

        // 라운드가 끝나면 2라운드로 설정
        BlazeBananani::get()->setGameStateValue("round", 2);

        // 배팅 여부 초기화
        BlazeBananani::get()->setGameStateValue("isBetting", 0 );
        
        // stStartOfRound
        $this->gamestate->nextState("start");
    }

    public function stStartOfMainTurn() {

        // 역할 지정하기 전 덱과 플레이어 카드의 수가 0이면 트로피 카드 획득
        $players = Players::getPlayers();
        $deckCount = Cards::getDeckCount();
        $is_betting = BlazeBananani::get()->getGameStateValue("isBetting");
        $trophy_card_id = BlazeBananani::get()->getGameStateValue('trophyCardId');

        foreach ($players as $player) {
            $count = Cards::countCards('hand', $player->getId());
            if ($deckCount <= 0 && $count <= 0 && $is_betting == 1 && $player->isEliminated() == false) {
                $player->eliminate(true);
                TrophyCards::moveCard($trophy_card_id, "hand", $player->getId());
                Notifications::getTrophyCard($player, $trophy_card_id);
                BlazeBananani::get()->setGameStateValue('trophyCardId', $trophy_card_id - 1);
            }
        }

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
        BlazeBananani::get()->setGameStateValue("nextOrder", 0);
        BlazeBananani::get()->setGameStateValue('isAttacked', 0 );
        BlazeBananani::get()->setGameStateValue('isDefensed', 0 );

        // 수비자 카드 수에 따른 공격 카드 제한
        $limitCount = is_null(Players::getPlayerWithRole(DEFENDER)['hand']) ? 0 : Players::getPlayerWithRole(DEFENDER)['hand'];
        BlazeBananani::get()->setGameStateValue('limitCount', $limitCount );

        // stStartOfSubTurn
        $this->gamestate->nextState("start");
    }

    public function stEndOfMainTurn() {
        $players = Players::getPlayers();
        $deckCount = Cards::getDeckCount();
        $is_betting = BlazeBananani::get()->getGameStateValue("isBetting");
        $is_defensed = BlazeBananani::get()->getGameStateValue("isDefensed");

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
        // 서브 턴 들어가기 전에 시작 하려는 플레이어의 카드가 없으면 다시 메인 시작 턴으로
        $player = Players::getActivePlayer();
        $is_betting = BlazeBananani::get()->getGameStateValue("isBetting");
        $active_player_card_count = Cards::countCards('hand', $player->getId());
        if ($active_player_card_count <= 0 && $is_betting == 1) {
            // stStartOfMainTurn
            $this->gamestate->nextState("end");
            return;
        }

        // stPlayerTurn
        $this->gamestate->nextState("start");
    }

    public function stEndOfSubTurn() {
        $is_defensed = BlazeBananani::get()->getGameStateValue("isDefensed");

        if ($is_defensed == DEFENSE_FAILURE || $is_defensed == DEFENSE_SUCCESS) {
            $this->gamestate->nextState("end");
            return;
        }

        // stStartOfSubTurn
        $this->gamestate->nextState("start");
    }
}