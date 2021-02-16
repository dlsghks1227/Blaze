<?php
namespace Blaze\States;

use BlazeBananani;
use Blaze\Players\Players;
use Blaze\Cards\Cards;
use Blaze\Game\Notifications;

trait PlayCardTrait
{
    public function stAttack() {
    }

    public function stSupport() {

    }

    public function stDefense() {
        
    }
    
    public function stNextPlayer() {

    }

    public function stBatting() {

    }

    public function attackCards($card_ids) {
        self::checkAction('attackCards');
        $cards = array_map(function($id){
            return Cards::getCard($id);
        }, $card_ids);
        $player = Players::getPlayer(self::getActivePlayerId());
        Notifications::attackCards($player, $cards);
    }
}