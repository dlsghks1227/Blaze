<?php
namespace Blaze\Players;

class Players extends \APP_GameClass
{
    public static function setupNewGame($gameInfos, $players)
    {
        self::DbQuery('DELETE FROM player');
        $sql = 'INSERT INTO player () VALUE';
    }
}