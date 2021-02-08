<?php
namespace blaze\Game;
use BlazeBananani;

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
    }
}