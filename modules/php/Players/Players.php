<?php
namespace BlazeBase\Players;

use Blaze;
use BlazeBase\Players\Player;

class Players extends \BlazeBase\Singleton
{
    public static function setupNewGame($players)
    {
        // 플레이어 데이터베이스 구성
        self::DbQuery('DELETE FROM player');
        $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_role) VALUE';
        
        $default_colors = array( "d1ab83", "ea7e24", "f96c98", "0fbbc1", "a969be" );
        foreach ($players as $player_id => $player) {
            $color      = array_shift( $default_colors );           // 색상
            $canal      = $player['player_canal'];
            $name       = addslashes($player['player_name']);       // 이름
            $avatar     = addslashes($player['player_avatar']);     // 아바타
            $role       = ROLE_NONE;
            $values[]   = "($player_id, '$color', '$canal', '$name', '$avatar', '$role')";
        }
        self::DbQuery($sql.implode($values, ','));
        
        // reattributeColorsBasedOnPreferences
        // 플레이어의 색상 기본 설정과 사용 가능한 색상을 고려해 모든 색상을 다시 지정
        Blaze::get()->reattributeColorsBasedOnPreferences($players, array( "d1ab83", "ea7e24", "f96c98", "0fbbc1", "a969be" ));
        Blaze::get()->reloadPlayersBasicInfos();
    }

    public static function getPlayers($players_id = null) : array
    {
        $columns = array("id", "no", "name", "no_card", "role", "color", "score", "zombie");
        $sql_columns = array();
        foreach($columns as $col) $sql_columns[] = "player_$col";
        $sql = "SELECT " . implode(", ", $sql_columns) . " FROM player";
        if (is_array($players_id)) {
            $sql .= " WHERE player_id IN ('" . implode("','", $players_id) . "')";
        }
        $rows = self::getObjectListFromDB($sql);

        $players = array();
        foreach ($rows as $row) {
            $players[] = new Player($row);
        }

        return $players;
    }

    public static function getPlayer($player_id) : Player
    {
        $players = self::getPlayers([$player_id]);
        return $players[0];
    }

    public static function getActivePlayer() : Player
    {
        return self::getPlayer(Blaze::get()->getActivePlayerId());
    }

    public static function getDatas($current_player = null) {
        return array_map(function($player) use ($current_player){
            return $player->getData($current_player);
        }, self::getPlayers());
    }

    public static function getPlayerWithRole($role)
    {
        $players = self::getPlayers();
        foreach ($players as $player)
        {
            if ($player->getRole() == $role)
            {
                return $player->getData();
            }
        }

        return null;
    }

    public static function getAlivePlayerCount() : int
    {
        $players = self::getPlayers();
        $count = 0;

        foreach ($players as $player)
        {
            if ($player->isEliminated() == false)
            {
                $count++;
            }
        }
        
        return $count;
    }

    public static function getNextRole($current_role)
    {
        $order = array(
            ROLE_NONE       => ROLE_DEFENDER,
            ROLE_DEFENDER   => ROLE_ATTACKER,
            ROLE_ATTACKER   => ROLE_SUPPORTER,
            ROLE_SUPPORTER  => ROLE_DEFENDER,
        );
        return $order[$current_role];
    }

    public static function updatePlayersRole($active_player_id)
    {
        $next_player_table = Blaze::get()->getNextPlayerTable();
        
        // 모든 플레이어의 역할 초기화
        $players = self::getPlayers();
        foreach ($players as $player)
        {
            $player->changeRole(ROLE_NONE);
        }

        // 공격자 정하기
        // 지정한 공격자가 제외되었을 때 다음 플레이어가 공격자
        $next_player_id = $active_player_id;
        for ($i = 0; $i < count($next_player_table) + 1; $i++)
        {
            if (self::getPlayer($next_player_id)->isEliminated() == true)
            {
                $next_player_id = $next_player_table[$next_player_id];
                continue;
            }

            self::getPlayer($next_player_id)->changeRole(ROLE_ATTACKER);

            break;
        }

        // 방어자 및 수비자 정하기
        $defender_selected = false;
        for ($i = 0; $i < count($next_player_table) + 1; $i++)
        {
            if (self::getPlayer($next_player_id)->isEliminated() == true ||
                self::getPlayer($next_player_id)->getRole() > ROLE_NONE)
            {
                $next_player_id = $next_player_table[$next_player_id];
                continue;
            }

            if ($defender_selected == false)
            {
                self::getPlayer($next_player_id)->changeRole(ROLE_DEFENDER);
                $defender_selected = true;
            }
            else
            {
                self::getPlayer($next_player_id)->changeRole(ROLE_SUPPORTER);
                break;
            }
        }
    }

    public static function changeActivePlayerWithRole($role)
    {
        $players = self::getPlayers();
        foreach ($players as $player)
        {
            if ($player->getRole() == $role)
            {
                Blaze::get()->gamestate->changeActivePlayer($player->getId());
                Blaze::get()->giveExtraTime($player->getId());
                return $player->getData();
            }
        }
    }
}