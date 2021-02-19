<?php
namespace Blaze\States;

use BlazeBananani;
use Blaze\Players\Players;
use Blaze\Cards\Cards;
use Blaze\Game\Log;
use Blaze\Game\Notifications;

trait PlayCardTrait
{   
    public function stNextPlayer() {
        $player_id = $this->activeNextPlayer();

        if (Players::getPlayer($player_id)->isEliminated()) {
            $this->stNextPlayer();
            return;
        }

        self::giveExtraTime($player_id);
        $this->gamestate->nextState('next');
    }

    public function argPlayerTurn() {
        return array(
            'tableOnAttackCards' => Cards::countCards('attackCards'),
            'activePlayerRole' => Players::getPlayer(self::getActivePlayerId())->getRole()
        );
    }

    public function attack($cards_id) {
        self::checkAction('attack');
        
        $cards = array_map(function($id){
            return Cards::getCard($id);
        }, $cards_id);

        $player = Players::getPlayer(self::getActivePlayerId());
        $player->attack($cards);

        $this->gamestate->nextState('next');
    }

    public function defense($cards_id) {
        self::checkAction('defense');

        $cards = array_map(function($id){
            return Cards::getCard($id);
        }, $cards_id);

        $player = Players::getPlayer(self::getActivePlayerId());
        $player->defense($cards);

        $this->gamestate->nextState('next');
    }

    public function support($cards_id) {
        self::checkAction('support');

        $cards = array_map(function($id){
            return Cards::getCard($id);
        }, $cards_id);

        $player = Players::getPlayer(self::getActivePlayerId());
        $player->attack($cards);

        $this->gamestate->nextState('next');
    }

    public function pass() {
        self::checkAction('pass');

        $this->gamestate->nextState('next');
    }
}