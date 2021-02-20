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

        // 라운드가 끝나면 2라운드로 설정
        BlazeBananani::get()->setGameStateValue("round", 2);

    }

    public function stStartOfMainTurn() {
        // 공격자 및 수비자, 지원자 지정
        // 공격자 = 1, 수비자 = 2, 지원자 = 3
        $player = Players::getActivePlayer();
        Players::updatePlayersRole($player->getId());

        // 순서 및 수비 성공 여부 초기화
        BlazeBananani::get()->setGameStateValue("nextOrder", 0);
        BlazeBananani::get()->setGameStateValue('isAttacked', 0 );
        BlazeBananani::get()->setGameStateValue('isDefensed', 0 );

        // stStartOfSubTurn
        $this->gamestate->nextState("");
    }

    public function stEndOfMainTurn() {
        $players = Players::getPlayers();
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

        $this->gamestate->nextState("start");
    }

    public function stStartOfSubTurn() {

        // stPlayerTurn
        $this->gamestate->nextState("");
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