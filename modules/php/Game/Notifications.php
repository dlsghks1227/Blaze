<?php
namespace Blaze\Game;

use BlazeBananani;
use Blaze\Cards\Cards;
use Blaze\Players\Players;

class Notifications {
    protected static function notifyAll($name, $message, $data) {
        BlazeBananani::get()->notifyAllPlayers($name, $message, $data);
    }

    protected static function notify($pID, $name, $message, $data) {
        BlazeBananani::get()->notifyPlayer($pID, $name, $message, $data);
    }

    public static function DrawCards($player_id, $cards) {
        $msg = clienttranslate("aa");
        $data = array(
            "cards" => $cards
        );
        self::notify($player_id, 'drawCard', $msg, $data);
    }

    public static function attackCards($player, $cards) {
        $msg = clienttranslate('${player_name} attacks with ${amount} cards');
        $cardsData = array_map(function($card){ return $card->getData(); }, $cards);
        self::notifyAll("attackCards", $msg, [
            'i18n' => array(),
            'player_name' => $player->getName(),
            'player_id' => $player->getId(),
            'amount' => count($cards),
            'cards' => $cardsData,
            'deckCount' => Cards::getDeckCount(),
        ]);
    }
}