<?php
namespace Blaze\States;

use BlazeBananani;
use Blaze\Players\Players;
use Blaze\Cards\Cards;
use Blaze\Game\Notifications;

trait DrawCardsTrait
{
    public function stDrawCards() {
    }

    public function placeCard($card_id) {
        self::checkAction('placeCard');
        BlazeBananani::get()->notifyAllPlayers('placeCard', 'asdasd', array(
            'card_id' => $card_id
        ));
        $this->gamestate->nextState('defense');
    }
}