<?php
namespace BlazeBase\States;

use Blaze;
use BlazeBase\Players\Players;
use BlazeBase\Cards\Cards;

trait SubTurnTrait
{
    // 1. 현재 역할을 불러와서 역할에 맞는 플레이어를 활성화
    public function stStartOfSubTurn()
    {
        // ----- 1 -----
        $current_role_order = Blaze::get()->getGameStateValue('roleOrder');
        Players::changeActivePlayerWithRole($current_role_order == ROLE_NONE ? ROLE_ATTACKER : $current_role_order);
        
        // playerTurn
        $this->gamestate->nextState('start');
    }

    // 1. 플레이한 플레이어가 카드가 없으면 트로피 카드를 드로우한다.
    //      - 남은 플레이어가 2명 이상일 때 제공
    //      - 트로피카드 제공 후 플레이어 제외
    // 2. 현재 플레이한 플레이어가 방어자이고 방어를 실패했을때 수비자까지 추가 공격
    //      - 방어 실패 상태와 현재 플레이한 플레이어가 수비자이면 'drawCard'로 상태 이동
    // 3. 방어를 성공하거나 남아있는 플레이어가 2명 이하이고 방어 실패했을 때 'drawCard'로 상태 이동
    // 4. 다음 역할이 공격자이고 공격자가 카드가 없으면 'drawCard'로 상태 이동
    //      - 배팅 이후 적용
    //      - 방어자가 방어 성공할 수 있으므로 방어를 했다면 방어 실패가 아닌 이상 성공으로 만든다.
    public function stEndOfSubTurn()
    {
        $active_player = Players::getActivePlayer();
        $is_betting = Blaze::get()->getGameStateValue("isBetting");
        $is_defensed = Blaze::get()->getGameStateValue("isDefensed");

        $alive_player_count = Players::getAlivePlayerCount();
        $active_player_card_count = $active_player->getData()['hand'];

        // ----- 1 -----
        if ($is_betting == 1 && 
            $alive_player_count >= 2 && 
            $active_player_card_count <= 0 && 
            $active_player->isEliminated() == false)
        {
            $current_round = Blaze::get()->getGameStateValue('round');

            $active_player->eliminate(true);
            Cards::drawTrophyCard($current_round, $active_player->getId());
        }

        // ----- 2 -----
        if ($is_defensed == DEFENSE_FAILURE)
        {
            if ($active_player->getRole() == ROLE_DEFENDER)
            {
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

        // ----- 3 -----
        if ($is_defensed == DEFENSE_SUCCESS || ($is_defensed == DEFENSE_FAILURE && $alive_player_count <= 2))
        {
            // drawCard
            $this->gamestate->nextState('end');
            return;
        }
        
        // ---- 4 ----
        $next_role_order = Blaze::get()->getGameStateValue('roleOrder');
        if ($next_role_order == ROLE_ATTACKER && $is_betting == 1)
        {
            $attacker_card_count = Players::getPlayerWithRole(ROLE_ATTACKER)['hand'];
            if (is_null($attacker_card_count) == false)
            {
                if ($attacker_card_count <= 0)
                {
                    if ($is_defensed != DEFENSE_FAILURE) {
                        Blaze::get()->setGameStateValue('isDefensed', DEFENSE_SUCCESS);
                    }
                    // drawCard
                    $this->gamestate->nextState('end');
                    return;
                }
            }
        }

        // startOfSubTurn
        $this->gamestate->nextState('start');
    }
}