<?php
namespace Blaze\Players;

use BlazeBananani;
use Blaze\Game\Log;
use Blaze\Players\Player;
use Blaze\Cards\Cards;

class Players extends \APP_GameClass
{
    public static function setupNewGame($players)
    {
        // 플레이어 데이터베이스 구성
        self::DbQuery('DELETE FROM player');
        $gameInfos = BlazeBananani::get()->getGameinfos();
        $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUE';
        
        $default_colors = $gameInfos['player_colors'];
        foreach ($players as $player_id => $player) {
            $color      = array_shift( $default_colors );           // 색상
            $canal      = $player['player_canal'];
            $name       = addslashes($player['player_name']);       // 이름
            $avatar     = addslashes($player['player_avatar']);     // 아바타
            $values[]   = "($player_id, '$color', '$canal', '$name', '$avatar')";
        }
        self::DbQuery($sql.implode($values, ','));
        
        // reattributeColorsBasedOnPreferences
        // 플레이어의 색상 기본 설정과 사용 가능한 색상을 고려해 모든 색상을 다시 지정
        BlazeBananani::get()->reattributeColorsBasedOnPreferences($players, $gameInfos['player_colors']);
        BlazeBananani::get()->reloadPlayersBasicInfos();
    }

    public static function GetCurrentTurn($as_object = false) {
        $player_id = Log::getPlayerTurn();
        return $as_object ? self::GetPlayer($player_id) : $player_id;
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