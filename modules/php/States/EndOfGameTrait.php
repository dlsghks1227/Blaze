<?php
namespace BlazeBase\States;

use BlazeBase\Players\Players;

trait EndOfGameTrait
{
    // 1. 최종 점수 업데이트
    public function stPreEndGame()
    {
        // ----- 1 -----
        $players = Players::getPlayers();

        foreach ($players as $player)
        {
            $player_score = $player->getScore();
            $player_id = $player->getId();
            self::DbQuery("UPDATE player SET player_score = $player_score WHERE player_id = $player_id");
        }
        // gameEnd
        $this->gamestate->nextState('end');
    }
}