<?php
namespace Blaze\States;

use Blaze\Game\Log;
use Blaze\Cards\Cards;
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

        }

        // 카드 맨 위에 있는 카드를 트럼프 슈트로 등록
        $trump_suit_card =  Cards::drawTrumpSuitCard();
        BlazeBananani::get()->setGameStateValue("trumpSuit", $trump_suit_card->GetType());

        // stStartOfMainTurn
        $this->gamestate->nextState("");
    }

    public function stEndOfRound() {

    }

    public function stStartOfMainTurn() {
        // 공격자 및 수비자, 지원자 지정
        // 공격자 = 1, 수비자 = 2, 지원자 = 3
        $player = Players::getActivePlayer();
        if ($player->getRole() == 0) {
            Players::updatePlayersRole($player->getId());
        } else {
            
        }

        // 공격 및 방어 상태 초기화
        BlazeBananani::get()->setGameStateValue("nextOrder", 0);

        // stStartOfSubTurn
        $this->gamestate->nextState("");
    }

    public function stEndOfMainTurn() {

    }

    public function stStartOfSubTurn() {
        // 공격 카드에 카드가 없으면 공격자 턴 시작
        $players = Players::getPlayers();
        $next_order = BlazeBananani::get()->getGameStateValue("nextOrder");
        $order = $next_order == 0 ? ATTACKER : $next_order;

        foreach ($players as $player) {
            if ($player->getRole() == $order) {
                $this->gamestate->changeActivePlayer($player->getId());
                break;
            }
        }

        BlazeBananani::get()->setGameStateValue("nextOrder", Players::getNextRole($next_order));
        
        // stPlayerTurn
        $this->gamestate->nextState("");
    }

    public function stEndOfSubTurn() {
        $attack_cards_count = Cards::countCards('attackCards');

        if ($attack_cards_count == 5) {
            $this->gamestate->nextState("end");
        }

        // stStartOfSubTurn
        $this->gamestate->nextState("start");
    }
}