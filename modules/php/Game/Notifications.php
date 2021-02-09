<?php
namespace Blaze\Game;

use BlazeBananani;
use Blaze\Players\Players;

class Notifications {
    protected static function notifyAll($name, $message, $data) {
        BlazeBananani::get()->notifyAllPlayers($name, $message, $data);
    }

    protected static function notify($player_id, $name, $message, $data) {
        BlazeBananani::get()->notifyPlayer($player_id, $name, $message, $data);
    }

    public static function DrawCards($player, $card) {
        // $data = array(
        //     'i18n' => array(''),
        //     'player_name' => BlazeBananani::get()->getActivePlayerName(),
        //     'value' => 
        // )
    }
}