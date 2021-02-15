<?php
namespace Blaze\Game;

use BlazeBananani;
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
}