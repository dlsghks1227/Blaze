<?php
namespace BlazeBase\Players;

use Blaze;
use BlazeBase\Game\Log;
use BlazeBase\Players\Player;
use BlazeBase\Cards\Cards;
use BlazeBase\Game\Notifications;

class Players extends \APP_GameClass
{
    public static function setupNewGame($players)
    {
        // 플레이어 데이터베이스 구성
        self::DbQuery('DELETE FROM player');
        $gameInfos = Blaze::get()->getGameinfos();
        $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_role) VALUE';
        
        $default_colors = $gameInfos['player_colors'];
        foreach ($players as $player_id => $player) {
            $color      = array_shift( $default_colors );           // 색상
            $canal      = $player['player_canal'];
            $name       = addslashes($player['player_name']);       // 이름
            $avatar     = addslashes($player['player_avatar']);     // 아바타
            $role       = 0;
            $values[]   = "($player_id, '$color', '$canal', '$name', '$avatar', '$role')";
        }
        self::DbQuery($sql.implode($values, ','));
        
        // reattributeColorsBasedOnPreferences
        // 플레이어의 색상 기본 설정과 사용 가능한 색상을 고려해 모든 색상을 다시 지정
        Blaze::get()->reattributeColorsBasedOnPreferences($players, $gameInfos['player_colors']);
        Blaze::get()->reloadPlayersBasicInfos();
    }

    public static function getCurrentTurn($as_object = false) {
        $player_id = Log::getPlayerTurn();
        return $as_object ? self::getPlayer($player_id) : $player_id;
    }

    public static function getActivePlayer() {
        return self::getPlayer(Blaze::get()->getActivePlayerId());
    }

    public static function getPlayer($player_id) {
        $players = self::getPlayers([$player_id]);
        return $players[0];
    }

    public static function getPlayers($players_id = null, $as_array_collection = false)
    {
        $columns = array("id", "no", "name", "eliminated", "role", "color", "score", "zombie");
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

    public static function getData($current_player) {
        return array_map(function($player) use ($current_player){
            return $player->getData($current_player);
        }, self::getPlayers());
    }

    public static function getPlayerWithRole($role) {
        $players = self::getPlayers();
        foreach ($players as $player) {
            if ($player->getRole() == $role) {
                return $player->getData();
            }
        }
    }

    public static function getNextRole($current_role) {
        $order = array(
            0 => DEFENDER,
            DEFENDER => ATTACKER,
            ATTACKER => VOLUNTEER,
            VOLUNTEER => DEFENDER,
        );
        return $order[$current_role];
    }

    public static function updatePlayersRole($player_id) {
        $player_table = Blaze::get()->getNextPlayerTable();

        // 여기서도 현재플레이어가 제외 된 상태면 다음 상태로 넘어가야한다..
        $next_player_id = $player_id;
        for ($i = 0; $i < count($player_table) + 1; $i++) {
            if (self::getPlayer($next_player_id)->isEliminated() == true) {
                $next_player_id = $player_table[$next_player_id];
                continue;
            }
            $attacker_player = self::getPlayer($next_player_id);
            $attacker_player->updateRole(ATTACKER);
            
            Blaze::get()->gamestate->changeActivePlayer($next_player_id);
            Blaze::get()->giveExtraTime($next_player_id);

            break;
        }
        
        $defenderSelected = false;
        for ($i = 0; $i < count($player_table) + 1; $i++) {
            if (self::getPlayer($next_player_id)->isEliminated() == true || 
                self::getPlayer($next_player_id)->getRole() > 0) {
                $next_player_id = $player_table[$next_player_id];
                continue;
            }
            if ($defenderSelected == false) {
                self::getPlayer($next_player_id)->updateRole(DEFENDER);
                $defenderSelected = true;
            } else {
                self::getPlayer($next_player_id)->updateRole(VOLUNTEER);
                break;
            }
        }

        Notifications::updatePlayers();
    }
}
