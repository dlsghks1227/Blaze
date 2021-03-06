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
 * states.inc.php
 *
 * Blaze game states description
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
    ST_GAME_SETUP => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( 
            "" => ST_START_OF_ROUND,
        ),
    ),

    ST_START_OF_ROUND => array(
        "name" => "startOfRound",
        "description" => "",
        "type" => "game",
        "action" => "stStartOfRound",
        "transitions" => array(
            "start" => ST_START_OF_MAIN_TURN,
        ),
    ),

    ST_END_OF_ROUND => array(
        "name" => "endOfRound",
        "description" => "",
        "type" => "game",
        "action" => "stEndOfRound",
        "transitions" => array(
            "start" => ST_START_OF_ROUND,
            "end" => ST_PRE_END_GAME,
        ),
    ),

    ST_START_OF_MAIN_TURN => array(
        "name" => "startOfMainTurn",
        "description" => "",
        "type" => "game",
        "action" => "stStartOfMainTurn",
        "transitions" => array(
            "start" => ST_START_OF_SUB_TURN,
        ),
    ),

    ST_END_OF_MAIN_TURN => array(
        "name" => "endOfMainTurn",
        "description" => "",
        "type" => "game",
        "action" => "stEndOfMainTurn",
        "transitions" => array(
            "start" => ST_START_OF_MAIN_TURN,
            "startbetting" => ST_START_OF_BETTING,
            "endRound" => ST_END_OF_ROUND
        ),
    ),

    ST_START_OF_SUB_TURN => array(
        "name" => "startOfSubTurn",
        "description" => "",
        "type" => "game",
        "action" => "stStartOfSubTurn",
        "transitions" => array(
            "start" => ST_PLAYER_TURN,
        ),
    ),

    ST_END_OF_SUB_TURN => array(
        "name" => "endOfSubTurn",
        "description" => "",
        "type" => "game",
        "action" => "stEndOfSubTurn",
        "transitions" => array(
            "start" => ST_START_OF_SUB_TURN,
            "end" => ST_DRAW_CARD,
        ),
    ),

    
    ST_PLAYER_TURN => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} can play a card'),
        "descriptionmyturn" => clienttranslate('${you} can play a card'),
        "type" => "activeplayer",
        "args" => "argPlayerTurn",
        "action" => "stPlayerTurn",
        "possibleactions" => array("attack", "defense", "support", "pass"),
        "transitions" => array(
            "zombiePass" => ST_NEXT_PLAYER,
            "next" => ST_NEXT_PLAYER,
        ),
    ),

    ST_NEXT_PLAYER => array(
        "name" => "nextPlayer",
        "description" => "",
        "type" => "game",
        "action" => "stNextPlayer",
        "transitions" => array(
            "start" => ST_END_OF_SUB_TURN,
        ),
    ),

    ST_DRAW_CARD => array(
        "name" => "drawCard",
        "description" => "",
        "type" => "game",
        "action" => "stDrawCard",
        "transitions" => array(
            "end" => ST_END_OF_MAIN_TURN,
        ),
    ),

    ST_START_OF_BETTING => array(
        "name" => "startOfBetting",
        "description" => clienttranslate('Pick a card and player to bet on'),
        "descriptionmyturn" => clienttranslate('Pick a card and player to bet on'),
        "type" => "multipleactiveplayer",
        "action" => "stStartOfBetting",
        "possibleactions" => array( "betting" ),
        "transitions" => array(
            "end" => ST_END_OF_BETTING,
        ),
    ),

    ST_END_OF_BETTING => array(
        "name" => "endOfBetting",
        "description" => "",
        "type" => "game",
        "action" => "stEndOfBetting",
        "transitions" => array(
            "start" => ST_START_OF_MAIN_TURN,
        ),
    ),


    ST_PRE_END_GAME => array(
        "name" => "preEndGame",
        "description" => "",
        "type" => "game",
        "action" => "stPreEndGame",
        "transitions" => array(
            "end" => ST_END_GAME,
        ),
    ),

    // Final state.
    // Please do not modify.
    ST_END_GAME => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);