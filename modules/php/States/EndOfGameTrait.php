<?php
namespace BlazeBase\States;

trait EndOfGameTrait
{
    // 1. 최종 점수 업데이트
    public function stPreEndGame()
    {
        // ----- 1 -----
        
        // gameEnd
        $this->gamestate->nextState('end');
    }
}