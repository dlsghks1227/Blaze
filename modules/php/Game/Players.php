<?php
namespace BlazeBase\Game;

use Blaze;
use BlazeBase\Cards\Cards;

class Players extends \APP_GameClass
{
    public static function setupNewGame($players)
    {
        self::DbQuery('DELETE FROM player');
        $gameInfos = Blaze::get()->getGameinfos();
        $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_role) VALUE';
        
        $default_colors = $gameInfos['player_colors'];
        foreach ($players as $player_id => $player) {
            $color      = array_shift( $default_colors );
            $canal      = $player['player_canal'];
            $name       = addslashes($player['player_name']);
            $avatar     = addslashes($player['player_avatar']);
            $values[]   = "($player_id, '$color', '$canal', '$name', '$avatar')";
        }
        self::DbQuery($sql.implode($values, ','));
        
        Blaze::get()->reattributeColorsBasedOnPreferences($players, $gameInfos['player_colors']);
        Blaze::get()->reloadPlayersBasicInfos();
    }

    public static function GetActivePlayer() {
        return self::GetPlayers(Blaze::get()->getActivePlayerId());
    }

    public static function GetPlayer($player_id) {
        $players = self::GetPlayers([$player_id]);
        return $players[0];
    }

    public static function GetPlayers($players_id = null, $as_array_collection = false)
    {
        $columns = array("id", "color", "name", "role", "score");
        $sql_columns = array();
        foreach($columns as $col) $sql_columns[] = "player_$col";
        $sql = "SELECT " . implode(", ", $sql_columns) . " FROM player";
        if (is_array($players_id)) {
            $sql .= " WHERE player_id IN ('" . implode("','", $players_id) . "')";
        }

        if ($as_array_collection) {
            return self::getCollectionFromDB($sql);
        }
        $rows = self::getObjectListFromDB($sql);

        return $rows;
    }
}
