<?php
namespace BlazeBase\States;

use Blaze;
use BlazeBase\Game\Notifications;
use BlazeBase\Players\Players;
use BlazeBase\Cards\Cards;

trait MainTurnTrait
{
    // 1. 플레이어 역할 정하기
    // 2. 공격 가능한 카드 수 설정 (방어자의 카드 수)
    // 3. 역할 순서 및 방어 성공 여부, 공격 여부 초기화
    public function stStartOfMainTurn()
    {
        // ----- 1 -----
        $attacker_id = Blaze::get()->getGameStateValue('startAttackerId');
        Players::updatePlayersRole($attacker_id);
        Notifications::changeRole(Players::getPlayers());

        // ----- 2 -----
        $defenser_card_count = Players::getPlayerWithRole(ROLE_DEFENDER)['hand'];
        if (is_null($defenser_card_count) == false)
        {
            Blaze::get()->setGameStateValue('limitCardCount', $defenser_card_count);
        }

        // ----- 3 -----
        Blaze::get()->setGameStateValue('roleOrder',    ROLE_NONE);
        Blaze::get()->setGameStateValue('isDefensed',   DEFENSE_NONE);
        Blaze::get()->setGameStateValue('isAttacked',   0);

        // startOfSubTurn
        $this->gamestate->nextState('start');
    }

    // 1. 덱에 있는 카드 수가 0이고 트럼프 카드가 존재하면 트럼프 카드를 덱으로 옮긴다.
    // 2. 덱에 있는 카드 수 확인 후 카드가 없고 배팅을 하지 않았다면 배팅 진행
    // 3. 현재 남아있는 플레이어가 1명일 때 라운드 종료
    // 4. 위의 조건이 맞지 않다면 방어 성공 여부에 따라 플레이어 설정 후 메인 턴 시작
    public function stEndOfMainTurn()
    {
        $is_betting = Blaze::get()->getGameStateValue('isBetting');

        // ----- 1 -----
        $deck_count = Cards::getCountCards('deck');
        if ($deck_count <= 0)
        {
            Cards::moveAllCards('trumpCard', 'deck');
        }

        // ----- 2 -----
        $deck_count = Cards::getCountCards('deck');
        if ($deck_count <= 0 && $is_betting == 0)
        {
            // startOfBetting
            $this->gamestate->nextState('startbetting');
            return;
        }

        // ----- 3 -----
        $alive_player_count = Players::getAlivePlayerCount();
        if ($alive_player_count <= 1)
        {
            // endOfRound
            $this->gamestate->nextState('endRound');
            return;
        }

        // ----- 4 -----
        $is_defensed = Blaze::get()->getGameStateValue('isDefensed');
        $next_attacker = $is_defensed == DEFENSE_SUCCESS ? ROLE_DEFENDER : ROLE_SUPPORTER;
        $next_attacker_id = Players::getPlayerWithRole($next_attacker)['id'];
        Blaze::get()->setGameStateValue('startAttackerId', $next_attacker_id);

        // startOfMainTurn
        $this->gamestate->nextState('start');
    }
}