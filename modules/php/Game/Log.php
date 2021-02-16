<?php
namespace Blaze\Game;

use BlazeBananani;

class Log extends \APP_GameClass
{
    // public static function getCurrentTure()
    // {
    //     $turns = self::getObjectListFromDb("SELECT turn FROM log WHERE 'action' = 'startTrun' ORDER BY log_id DESC");
    //     return empty($turns) ? 0 : (int)$turns[0]["turn"];
    // }
    public static function getCurrentTurn() {
        $turns = self::getObjectListFromDb("SELECT turn FROM log WHERE `action` = 'startTurn' ORDER BY log_id DESC");
        return empty($truns) ? 0 : (int)$turns[0]["turn"];
    }

    public static function insert($player_id, $card_id, $action, $args = array()) {
        $player_id = $player_id == -1 ? BlazeBananani::get()->getActivePlayerId() : $player_id;
        $turn = self::getCurrentTurn() + ($action == "startTurn" ? 1 : 0);
        $actionArgs = json_encode($args);
        self::DbQuery("INSERT INTO log (`turn`, `player_id`, `card_id`, `action`, `action_arg`) VALUES ('$turn', '$player_id', '$card_id', '$action', '$actionArgs')");
    }

    public static function addAction($action, $args = array()) {
        self::insert(-1, 0, $action, $args);
    }

    public static function startTurn() {
        self::insert(-1, 0, 'startTurn');
    }

    public static function getPlayerTurn() {
        $turn = self::getObjectFromDb("SELECT * FROM log WHERE 'action' = 'startTurn' ORDER BY log_id DESC LIMIT 1");
        return is_null($turn) ? null : $turn['player_id'];
    }
}