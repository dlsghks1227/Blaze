<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * BlazeBananani implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * BlazeBananani game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

 
$machinestates = array(

    // The initial state. Please do not modify.
    STATE_GAME_SETUP => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( 
            "" => STATE_START_OF_ROUND_ONE 
        )
    ),
    
    // Note: ID=2 => your first state

    STATE_START_OF_ROUND_ONE => array(
        "name" => "startOfRoundOne",
        "description" => '',
        "type" => "game",
        "action" => 'stStartOfRoundOne',
        "updateGameProgression" => true,
        "transitions" => array(
            "" => STATE_ATTACK
        )
    ),

    STATE_START_OF_ROUND_TWO => array(
        "name" => "startOfRoundTwo",
        "description" => '',
        "type" => "game",
        "action" => 'stStartOfRoundTwo',
        "updateGameProgression" => true,
        "transitions" => array(
            "" => STATE_ATTACK
        )
    ),

    STATE_DRAW_CARDS => array(
        "name" => "drawCards",
        "description" => '',
        "type" => 'game',
        "action" => 'stDrawCards',
        "transitions" => array(
        )
    ),

    STATE_ATTACK => array(
        "name" => "attack",
        "description" => '',
        "descriptionmyturn" => '',
        "type" => 'activeplayer',
        "action" => 'stAttack',
        "possibleactions" => array( "placeCard" ),
        "transitions" => array(
            "defense" => STATE_DEFENSE,
            "support" => STATE_SUPPORT
        )
    ),

    STATE_SUPPORT => array(
        "name" => "support",
        "description" => '',
        "descriptionmyturn" => '',
        "type" => 'activeplayer',
        "action" => 'stSupport',
        'transitions' => array(
        )
    ),
    
    STATE_DEFENSE => array(
        "name" => "defense",
        "description" => '',
        "descriptionmyturn" => '',
        "type" => 'activeplayer',
        "action" => 'stDefense',
        "transitions" => array(
            'success' => STATE_DEFENSE_SUCCESS,
            'failure' => STATE_DEFENSE_FAILURE,
        )
    ),

    STATE_DEFENSE_SUCCESS => array(
        "name" => "defenseSuccess",
        "description" => '',
        "type" => 'game',
        "action" => 'stDefenseSuccess',
        'transitions' => array(
            'endTurn' => STATE_DEFENSE_FAILURE,
        )
    ),

    STATE_DEFENSE_FAILURE => array(
        "name" => "defenseFailure",
        "description" => '',
        "type" => 'game',
        "action" => 'stDefenseFailure',
        'transitions' => array(
            'endTurn' => STATE_DEFENSE_FAILURE,
        )
    ),

    STATE_END_OF_TURN => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => 'game',
        "action" => '',
        'transitions' => array(
            'next' => STATE_NEXT_PLAYER
        )
    ),

    STATE_NEXT_PLAYER => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => 'game',
        "action" => 'stNextPlayer',
        'transitions' => array(
            'endRound' => STATE_END_ROUND
        )
    ),

    STATE_END_ROUND => array(
        "name" => "endRound",
        "description" => '',
        "type" => 'game',
        "action" => '',
        'transitions' => array(
            'batting' => STATE_BATTING
        )
    ),

    STATE_BATTING => array(
        "name" => "batting",
        "description" => '',
        'descriptionmyturn' => '',
        "type" => 'multipleactiveplayer',
        "action" => '',
        'transitions' => array(
        )
    ),
    
/*
    Examples:
    
    2 => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,   
        "transitions" => array( "endGame" => 99, "nextPlayer" => 10 )
    ),
    
    10 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must play a card or pass'),
        "descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
        "type" => "activeplayer",
        "possibleactions" => array( "playCard", "pass" ),
        "transitions" => array( "playCard" => 2, "pass" => 2 )
    ), 

*/    
   
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    STATE_END_GAME => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



