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

    public static function attack($player, $cards) {
        $msg = clienttranslate('${player_name} attacks with ${amount} cards');
        $cardsData = array_map(function($card){ return $card->getData(); }, $cards);
        self::notifyAll("attack", $msg, [
            'i18n' => array(),
            'player_name' => $player->getName(),
            'player_id' => $player->getId(),
            'amount' => count($cards),
            'cards' => $cardsData,
            'deckCount' => Cards::getDeckCount(),
        ]);
    }

    public static function defense($player, $cards) {
        $msg = clienttranslate('${player_name} defense with ${amount} cards');
        $cardsData = array_map(function($card){ return $card->getData(); }, $cards);
        self::notifyAll("defense", $msg, [
            'i18n' => array(),
            'player_name' => $player->getName(),
            'player_id' => $player->getId(),
            'amount' => count($cards),
            'cards' => $cardsData,
            'deckCount' => Cards::getDeckCount(),
        ]);
    }

    public static function pass($player) {
        $msg = clienttranslate('${player_name} passed');
        self::notifyAll("pass", $msg, array(
            'i18n' => array(),
            'player_name' => $player->getName(),
            'player_id' => $player->getId(),
            'deckCount' => Cards::getDeckCount(),
        ));
    }

    public static function updatePlayers() {

    }

    public static function changeRole($player) {
        $msg = clienttranslate('${player_name} is the ${role_text}');
        self::notifyAll("changeRole", $msg, [
            'i18n' => array('role_text'),
            'player_name' => $player->getName(),
            'player_id' => $player->getId(),
            'role' => $player->getRole(),
            'role_text' => $player->getRoleFormat(),
            'deckCount' => Cards::getDeckCount(),
        ]);
    }
}