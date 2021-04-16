<?php
namespace BlazeBase\States;

use Blaze;
use BlazeBase\Players\Players;
use BlazeBase\Cards\Cards;

trait RoundTrait
{
    // 1. 라운드에 사용할 카드 및 트럼프 카드, 버려진 카드 설정
    //      - DB에 저장해서 하는 방법도 있지만 지금 만들기 늦었으니 그냥 쓴다!
    public function stStartOfRound()
    {
        // ----- 1 -----
        $current_round = Blaze::get()->getGameStateValue('round');
        $trump_card = Cards::roundSetting($current_round);

        Blaze::get()->setGameStateValue('trumpCardColor', $trump_card->getColor());
        Blaze::get()->setGameStateValue('trumpCardValue', $trump_card->getValue());

        Blaze::get()->setGameStateValue('discardCardColor', BLUE);
        Blaze::get()->setGameStateValue('discardCardValue', -1);

        // startOfMainTurn
        $this->gamestate->nextState('start');
    }

    // 1. 마지막까지 살아남은 플레이어가 다음 라운드 선공이므로 공격자 ID 설정 및 제외된 플레이어 재설정
    // 2. 마지막 플레이어 ID를 이용해 배팅 카드 정산
    // 3. 배팅 카드 정산 후 2 라운드일 경우 게임 종료
    // 4. 배팅 여부 초기화 및 2 라운드 설정
    public function stEndOfRound()
    {
        // ----- 1 -----
        $last_player_id = 0;
        $players = Players::getPlayers();
        foreach ($players as $player)
        {
            if ($player->isEliminated() == false)
            {
                $last_player_id = $player->getId();
            }
            $player->eliminate(false);
        }

        // ----- 2 -----
        Cards::resultBettingCard($last_player_id);

        // ----- 3 -----
        $current_round = Blaze::get()->getGameStateValue('round');
        if ($current_round >= 2)
        {
            // preEndGame
            $this->gamestate->nextState('end');
            return;
        }


        // ----- 4 -----
        Blaze::get()->setGameStateValue('round',            2);
        Blaze::get()->setGameStateValue('isBetting',        0);
        Blaze::get()->setGameStateValue('startAttackerId',  $last_player_id);

        // startOfRound
        $this->gamestate->nextState('start');
    }
}