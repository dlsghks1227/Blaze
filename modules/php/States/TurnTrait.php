<?php
namespace Blaze\States;

use Blaze\Cards\Cards;
use Blaze\Game\Notifications;

use BlazeBananani;

trait TurnTrait
{
    public function stStartOfRoundOne() {
        // 플레이어 기본 정보 불러오기
        $players = BlazeBananani::get()->loadPlayersBasicInfos();

        // 플레이어 당 5장의 카드 배분
        foreach ($players as $player_id => $player) {
            $cards = Cards::Draw(5, $player_id);
        }

        // 카드 배분 후 반으로 나누어 1라운드 진행
        $current_deck_count = Cards::GetDeckCount();
        Cards::MoveToTemplateDeck(round($current_deck_count / 2));

        // 카드 맨 위에 있는 카드를 트럼프 슈트로 등록

        $this->gamestate->nextState("");
    }

    public function stStartOfRoundTwo() {
        
    }

    public function stDefenseSuccess() {

    }

    public function stDefenseFailure() {
        
    }

    public function stEndRound() {

    }
}