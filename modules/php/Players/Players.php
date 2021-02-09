<?php
namespace Blaze\Players;

use BlazeBananani;
use Blaze\Players\Player;
use Blaze\Cards\Cards;

class Players extends \APP_GameClass
{
    public static function setupNewGame($players)
    {
        self::DbQuery('DELETE FROM player');
        $gameInfos = BlazeBananani::get()->getGameinfos();
        $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUE';
        
        $default_colors = $gameInfos['player_colors'];
        foreach ($players as $player_id => $player) {
            $color      = array_shift( $default_colors );
            $canal      = $player['player_canal'];
            $name       = addslashes($player['player_name']);
            $avatar     = addslashes($player['player_avatar']);
            $values[]   = "($player_id, '$color', '$canal', '$name', '$avatar')";
        }
        self::DbQuery($sql.implode($values, ','));
        
        BlazeBananani::get()->reattributeColorsBasedOnPreferences($players, $gameInfos['player_colors']);
        BlazeBananani::get()->reloadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            Cards::Draw(4, $player_id);
        }
    }

    public static function GetActivePlayer() {
        return self::GetPlayers(BlazeBananani::get()->getActivePlayerId());
    }

    public static function GetPlayer($player_id) {
        $players = self::GetPlayers([$player_id]);
        return $players[0];
    }

    public static function GetPlayers($players_id = null, $as_array_collection = false)
    {
        $columns = array("id", "no", "name", "color", "score");
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

        $players = array();
        foreach ($rows as $row) {
            $players[] = new Player($row);
        }

        return $players;
    }
}