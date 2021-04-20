<?php
namespace BlazeBase\States;

use Blaze;
use BlazeBase\Game\Notifications;
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
    // 2. 플레이한 플레이어가 카드가 없으면 트로피 카드를 드로우한다.
    public function stEndOfBetting()
    {
        // ----- 1 -----
        Blaze::get()->setGameStateValue('isBetting', 1);
        
        Notifications::endBetting();

        // ----- 2 -----
        $players = Players::getPlayers();
        foreach ($players as $player) {
            $alive_player_count = Players::getAlivePlayerCount();
            $player_hand_count = Cards::getCountCards('hand', $player->getId());
            if ($alive_player_count >= 2 && 
                $player_hand_count <= 0 && 
                $player->isEliminated() == false)
            {
                $current_round = Blaze::get()->getGameStateValue('round');
    
                $player->eliminate(true);
                $trophy_card = Cards::drawTrophyCard($current_round, $player->getId());
                Notifications::drawTrophyCard($player, $trophy_card);
            }
        }
        
        // startOfMainTurn
        $this->gamestate->nextState('start');
    }

    // 1. 카드 배팅
    public function betting($card_id, $selected_player_id)
    {
        self::checkAction('betting');
        $player_id = self::getCurrentPlayerId();

        // ----- 1 -----
        $betting_card = Cards::bettingCard($card_id, $selected_player_id);
        Players::getPlayer($player_id)->betting($betting_card, $selected_player_id);

        // endOfBetting
        $this->gamestate->setPlayerNonMultiactive($player_id, 'end');
    }
}