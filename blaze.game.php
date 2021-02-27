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

$swdNamespaceAutoLoad = function($class)
{
    $classPaths = explode('\\', $class);
    if ($classPaths[0] == 'BlazeBase')
    {
        array_shift($classPaths);
        $file = dirname(__FILE__)."/modules/php/".implode(DIRECTORY_SEPARATOR, $classPaths).".php";
        if (file_exists($file))
        {
            require_once($file);
        }
        else
        {
            var_dump("Impossible to load blaze class : $file");
        }
    }
};

spl_autoload_register($swdNamespaceAutoLoad, true, true);

require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );

// PHP Class
use BlazeBase\Cards\Cards;
use BlazeBase\Cards\BattingCards;
use BlazeBase\Cards\TrophyCards;

use BlazeBase\Players\Players;
use BlazeBase\Game\Notifications;

class Blaze extends Table
{
    use BlazeBase\States\TurnTrait;
    use BlazeBase\States\PlayCardTrait;
    use BlazeBase\States\DrawCardsTrait;

    public static $instance = null;
	public function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        self::$instance = $this;
        
        self::initGameStateLabels(array(
            "trumpSuitType" => 10,
            "trumpSuitValue" => 11,
            "round" => 12,
            "nextOrder" => 13,
            "isAttacked" => 14,
            "isDefensed" => 15,
            "isBetting" => 16,
            "trophyCardId" => 17,
            "limitCount" => 18
        ));        
	}
    public static function get()
    {
        return self::$instance;
    }
    public static function getCurrentId()
    {
        return self::getCurrentPlayerId();
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
        Players::setupNewGame($players);

        Cards::SetupNewGame(self::getPlayersNumber());
        TrophyCards::setupNewGame(self::getPlayersNumber());
        BattingCards::setupNewGame($players);
        
        // Init global values with their initial values
        self::setGameStateInitialValue('trumpSuitType', BLUE );
        self::setGameStateInitialValue('trumpSuitValue', 0 );
        self::setGameStateInitialValue('round', 1 );
        self::setGameStateInitialValue('nextOrder', 0 );
        self::setGameStateInitialValue('isAttacked', 0 );
        self::setGameStateInitialValue('isDefensed', 0 );
        self::setGameStateInitialValue('isBetting', 0 );
        self::setGameStateInitialValue('trophyCardId', 0 );
        self::setGameStateInitialValue('limitCount', 0 );
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // TODO: setup the initial game situation here
       

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
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
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $result = array(
            'playersInfo'   => Players::getData(self::getCurrentPlayerId()),
            'playerCount'   => self::getPlayersNumber(),
            'playerTurn'    => Players::getCurrentTurn(),
            
            'deckCards'     => Cards::getAllCardsInDeck(),
            'discardCards'  => Cards::getDiscardCards(),
            'attackCards'   => Cards::getAttackCards(),
            'defenseCards'  => Cards::getDefenseCards(),
            'trumpSuitCard' => Cards::getTrumpSuitCard(),

            'trophyCards'           => TrophyCards::getDeckCards(),
            'trophyCardsOnPlayer'   => TrophyCards::getHandCards(),

            'tokenCards'    => BattingCards::getHandCards(),
            'bettingCards'  => BattingCards::getBettingCards(),
            'bettedCards'   => BattingCards::getBettedCards(),

            'currentRound'  => Blaze::get()->getGameStateValue("round")
        );
  
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
  
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
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                case 'playerTurn':
                    $player = Players::getActivePlayer();

                    $player->eliminate(true);
                    
                    $this->gamestate->nextState( "zombiePass" );
                    break;
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, 'zombiePass' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
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