<?php
namespace BlazeBase\States;

use Blaze;
use BlazeBase\Players\Players;
use BlazeBase\Cards\Cards;
use BlazeBase\Game\Notifications;

trait SubTurnTrait
{
    // 1. 현재 역할을 불러와서 역할에 맞는 플레이어를 활성화
    public function stStartOfSubTurn()
    {
        // ----- 1 -----
        $current_role_order = Blaze::get()->getGameStateValue('roleOrder');

        $attacker_player = Players::getPlayerWithRole(ROLE_ATTACKER);
        if (is_null($attacker_player) == false && $current_role_order == ROLE_ATTACKER)
        {
            if ($attacker_player['eliminated'] == true)
            {
                $current_role_order = ROLE_SUPPORTER;
            }
        }

        Players::changeActivePlayerWithRole($current_role_order == ROLE_NONE ? ROLE_ATTACKER : $current_role_order);
        
        // playerTurn
        $this->gamestate->nextState('start');
    }

    // 1. 플레이한 플레이어가 카드가 없으면 트로피 카드를 드로우한다.
    //      - 남은 플레이어가 2명 이상일 때 제공
    //      - 트로피카드 제공 후 플레이어 제외
    // 2. 플레이어가 1명 남았을 때 'drawCard'로 상태 이동
    // 3. 방어자가 카드가 다 떨어지면 'drawCard'로 상태 이동
    // 3. 다음 역할이 공격자이고 공격자가 카드가 없으면 'drawCard'로 상태 이동
    //      - 배팅 이후 적용
    //      - 방어자가 방어 성공할 수 있으므로 방어를 했다면 방어 실패가 아닌 이상 성공으로 만든다.
    // 4. 현재 플레이한 플레이어가 방어자이고 방어를 실패했을때 수비자까지 추가 공격
    //      - 방어 실패 상태와 현재 플레이한 플레이어가 수비자이면 'drawCard'로 상태 이동
    // 5. 방어를 성공하거나 남아있는 플레이어가 2명 이하이고 방어 실패했을 때 'drawCard'로 상태 이동
    public function stEndOfSubTurn()
    {
        $active_player = Players::getActivePlayer();
        $is_betting = Blaze::get()->getGameStateValue("isBetting");
        $is_defensed = Blaze::get()->getGameStateValue("isDefensed");

        $alive_player_count = Players::getAlivePlayerCount();
        $previous_alive_player_count = $alive_player_count;
        $active_player_card_count = $active_player->getData()['hand'];

        Blaze::get()->setGameStateValue('previousAlivePlayer', $alive_player_count);

        // ----- 1 -----
        if ($is_betting == 1 && 
            $alive_player_count >= 2 && 
            $active_player_card_count <= 0 && 
            $active_player->isEliminated() == false)
        {
            $current_round = Blaze::get()->getGameStateValue('round');

            $active_player->eliminate(true);
            $trophy_card = Cards::drawTrophyCard($current_round, $active_player->getId());
            Notifications::drawTrophyCard($active_player, $trophy_card);
        }

        // ---- 2 ----
        $alive_player_count = Players::getAlivePlayerCount();
        if ($alive_player_count <= 1)
        {
            Blaze::get()->setGameStateValue('isDefensed', DEFENSE_SUCCESS);

            // drawCard
            $this->gamestate->nextState('end');
            return;
        }

        // ---- 3 ----
        if ($active_player->getRole() == ROLE_DEFENDER && $is_betting == 1)
        {
            $defender_card_count = Players::getPlayerWithRole(ROLE_DEFENDER)['hand'];
            if (is_null($defender_card_count) == false)
            {
                if ($defender_card_count['hand'] <= 0)
                {
                    Blaze::get()->setGameStateValue('isDefensed', DEFENSE_SUCCESS);

                    // drawCard
                    $this->gamestate->nextState('end');
                    return;
                }
            }
        }

        // ---- 4 ----
        // $next_role_order = Blaze::get()->getGameStateValue('roleOrder');
        // if ($next_role_order == ROLE_ATTACKER && $is_betting == 1)
        // {
        //     $attacker_card_count = Players::getPlayerWithRole(ROLE_ATTACKER)['hand'];

        //     if (is_null($attacker_card_count) == false)
        //     {
        //         if ($attacker_card_count <= 0 && $previous_alive_player_count <= 2)
        //         {
        //             if ($is_defensed != DEFENSE_FAILURE) {
        //                 Blaze::get()->setGameStateValue('isDefensed', DEFENSE_SUCCESS);
        //             }
        //             // drawCard
        //             $this->gamestate->nextState('end');
        //             return;
        //         }
        //     }
        // }

        // ----- 5 -----
        if ($is_defensed == DEFENSE_FAILURE)
        {
            if ($active_player->getRole() == ROLE_DEFENDER)
            {
                $support_player = Players::getPlayerWithRole(ROLE_SUPPORTER);
                if (is_null($support_player) == true)
                {
                    // drawCard
                    $this->gamestate->nextState('end');
                    return;
                }

                // startOfSubTurn
                $this->gamestate->nextState('start');
                return;
            }
            else if ($active_player->getRole() == ROLE_SUPPORTER)
            {
                // drawCard
                $this->gamestate->nextState('end');
                return;
            }
        }

        // ----- 6 -----
        if ($is_defensed == DEFENSE_SUCCESS)
        {
            // drawCard
            $this->gamestate->nextState('end');
            return;
        }

        // startOfSubTurn
        $this->gamestate->nextState('start');
    }
}