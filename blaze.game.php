<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * Blaze implementation : © <Inhwan Lee> <dlsghks1227@gmail.com>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * blaze.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */

$swdNamespaceAutoload = function ($class) {
    $classParts = explode('\\', $class);
    if ($classParts[0] == 'BlazeBase') {
        array_shift($classParts);
        $file = dirname(__FILE__) . '/modules/php/' . implode(DIRECTORY_SEPARATOR, $classParts) . '.php';
        if (file_exists($file)) {
            require_once $file;
        } else {
            var_dump("Impossible to load blaze class : $class");
        }
    }
};
spl_autoload_register($swdNamespaceAutoload, true, true);
  
require_once APP_GAMEMODULE_PATH . 'module/table/table.game.php';

/*
 *  PHP Class
 */
use BlazeBase\Game\Notifications;

use BlazeBase\Cards\Cards;
use BlazeBase\Players\Players;

class Blaze extends Table
{
    use BlazeBase\States\RoundTrait;
    use BlazeBase\States\MainTurnTrait;
    use BlazeBase\States\SubTurnTrait;
    use BlazeBase\States\PlayCardTrait;
    use BlazeBase\States\PlayerActionTrait;
    use BlazeBase\States\BettingTrait;
    use BlazeBase\States\EndOfGameTrait;

    public static $instance = null;
	public function __construct()
	{
        parent::__construct();
        self::$instance = $this;
        
        self::initGameStateLabels( array(
            'round'             => 10,
            'roleOrder'         => 11,
            'startAttackerId'   => 12,
            'limitCardCount'    => 13,
            'isBetting'         => 14,
            'isDefensed'        => 15,
            'isAttacked'        => 16,
            'trumpCardColor'    => 17,
            'trumpCardValue'    => 18
        ) );
	}

    public static function get()
    {
        return self::$instance;
    }
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "blaze";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {
        /************ Start the game initialization *****/
        Players::getInstance()->setupNewGame($players);
        Cards::getInstance()->setupNewGame($players);
        
        $this->activeNextPlayer();
        
        /************ End of the game initialization *****/
        self::setGameStateInitialValue('round',             1);
        self::setGameStateInitialValue('roleOrder',         ROLE_NONE);
        self::setGameStateInitialValue('startAttackerId',   Players::getActivePlayer()->getId());
        self::setGameStateInitialValue('limitCardCount',    0);
        self::setGameStateInitialValue('isBetting',         0);
        self::setGameStateInitialValue('isDefensed',        DEFENSE_NONE);
        self::setGameStateInitialValue('isAttacked',        0);
        self::setGameStateInitialValue('trumpCardColor',    BLUE);
        self::setGameStateInitialValue('trumpCardValue',    1);
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $current_player_id = self::getCurrentPlayerId();
        $current_round = self::get()->getGameStateValue('round');
        $trumpCardData = array(
            'color' => self::get()->getGameStateValue('trumpCardColor'),
            'value' => self::get()->getGameStateValue('trumpCardValue'),
        );

        $result = array( 
            'blazePlayers'      => Players::getDatas($current_player_id),
            'nextPlayerTable'   => self::getNextPlayerTable(),

            // card on table
            'deckCount'         => Cards::getCountCards('deck'),
            'trumpCard'         => $trumpCardData,
            'trophyCards'       => Cards::getCardsInLocation('trophy_deck_' . $current_round),
            
            // attack card and defense card on table
            'attackCardsOnTable'    => Cards::getAttackCardsOnTable(),
            'defenseCardsOnTable'   => Cards::getCardsInLocation('defenseCards'),
        );
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0.5;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */



//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in blaze.action.php)
    */

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] == "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] == "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $sql = "
                UPDATE  player
                SET     player_is_multiactive = 0
                WHERE   player_id = $active_player
            ";
            self::DbQuery( $sql );

            $this->gamestate->updateMultiactiveOrNextState( '' );
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }

    // function zombieTurn( $state, $active_player )
    // {
    // 	$statename = $state['name'];
    	
    //     if ($state['type'] === "activeplayer") {
    //         switch ($statename) {
    //             case 'playerTurn':
    //                 $player = Players::getActivePlayer();

    //                 $player->eliminate(true);
                    
    //                 $this->gamestate->nextState( "zombiePass" );
    //                 break;
    //             default:
    //                 $this->gamestate->nextState( "zombiePass" );
    //             	break;
    //         }

    //         return;
    //     }

    //     if ($state['type'] === "multipleactiveplayer") {
    //         // Make sure player is in a non blocking status for role turn
    //         $this->gamestate->setPlayerNonMultiactive( $active_player, 'zombiePass' );
            
    //         return;
    //     }

    //     throw new feException( "Zombie mode not supported at this game state: ".$statename );
    // }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
