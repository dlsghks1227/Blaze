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
        $players = Players::getPlayers();
        $next_order = BlazeBananani::get()->getGameStateValue("nextOrder");
        $order = Players::getNextRole($next_order);
        
        foreach ($players as $player) {
            if ($player->getRole() == $order) {
                $player_id = $player->getId();
                $this->gamestate->changeActivePlayer($player_id);
                self::giveExtraTime($player_id);
                break;
            }
        }

        BlazeBananani::get()->setGameStateValue("nextOrder", Players::getNextRole($next_order));

        // stEndOfSubTurn
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
        BlazeBananani::get()->setGameStateValue('isAttacked', 1 );
        $player->attack($cards);

        // stNextPlayer
        $this->gamestate->nextState('next');
    }

    public function defense($cards_id) {
        self::checkAction('defense');

        $cards = array_map(function($id){
            return Cards::getCard($id);
        }, $cards_id);

        $player = Players::getPlayer(self::getActivePlayerId());
        BlazeBananani::get()->setGameStateValue('isAttacked', 0 );

        $player->defense($cards);

        // stNextPlayer
        $this->gamestate->nextState('next');
    }

    public function support($cards_id) {
        self::checkAction('support');

        $cards = array_map(function($id){
            return Cards::getCard($id);
        }, $cards_id);

        $player = Players::getPlayer(self::getActivePlayerId());
        BlazeBananani::get()->setGameStateValue('isAttacked', 1 );
        $player->attack($cards);

        // stNextPlayer
        $this->gamestate->nextState('next');
    }

    public function pass() {
        self::checkAction('pass');
        $player = Players::getActivePlayer();
        $player_role = $player->getRole();
        $is_attacked = BlazeBananani::get()->getGameStateValue('isAttacked');

        if ($player_role == DEFENDER) {
            BlazeBananani::get()->setGameStateValue('isDefensed', DEFENSE_FAILURE );
        } else if ($player_role == VOLUNTEER) {
            if ($is_attacked == 0) {
                BlazeBananani::get()->setGameStateValue('isDefensed', DEFENSE_SUCCESS );
            }
        }

        // stNextPlayer
        $this->gamestate->nextState('next');
    }
}