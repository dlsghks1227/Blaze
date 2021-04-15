<?php
namespace BlazeBase\States;

use Blaze;
use BlazeBase\Players\Players;
use BlazeBase\Cards\Cards;

trait BettingTrait
{
    // 1. 모든 플레이어 활설화
    public function stStartOfBetting()
    {
        // ----- 1 -----
        $this->gamestate->setAllPlayersMultiactive();
    }

    // 1. 배팅 여부 활성화
    public function stEndOfBetting()
    {
        // ----- 1 -----
        Blaze::get()->setGameStateValue('isBetting', 2);
        
        // startOfMainTurn
        $this->gamestate->nextState('start');
    }

    // 1. 카드 배팅
    public function betting($card_id, $selected_player_id)
    {
        self::checkAction('attack');
        $player_id = self::getCurrentPlayerId();

        // ----- 1 -----
        Cards::bettingCard($card_id, $selected_player_id);

        // endOfBetting
        $this->gamestate->setPlayerNonMultiactive($player_id, 'end');
    }
}