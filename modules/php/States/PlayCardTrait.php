<?php
namespace BlazeBase\States;

use Blaze;
use BlazeBase\Players\Players;
use BlazeBase\Cards\Cards;

trait PlayCardTrait
{
    // 1. 공격자, 방어자, 지원자 순으로 카드를 드로우한다.
    //      - 배팅 후 이면 카드 드로우는 하지 않는다.
    public function stDrawCard()
    {
        // ----- 1 -----
        $is_defensed = Blaze::get()->getGameStateValue("isDefensed");
        $is_betting = Blaze::get()->getGameStateValue("isBetting");

        for ($role = ROLE_ATTACKER; $role <= ROLE_SUPPORTER; $role++)
        {
            $player_data = Players::getPlayerWithRole($role);
            if (is_null($player_data) == false)
            {
                $player_id = $player_data['id'];
                $amount = 5 - Cards::getCountCards('hand', $player_id);
                if ($role == ROLE_DEFENDER)
                {
                    Cards::moveUsedCards($player_id, $is_defensed);
                    if ($is_defensed == DEFENSE_SUCCESS)
                    {
                        if ($is_betting == 0)
                        {
                            Players::getPlayer($player_id)->drawCards($amount);
                        }
                    }
                }
                else
                {
                    if ($is_betting == 0)
                    {
                        Players::getPlayer($player_id)->drawCards($amount);
                    }
                }
            }
        }
        
        // endOfMainTurn
        $this->gamestate->nextState('end');
    }
}