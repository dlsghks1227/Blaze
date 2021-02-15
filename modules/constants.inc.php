<?php

/*
 *  State constants
 */
define('STATE_GAME_SETUP', 1);

define('STATE_START_OF_ROUND_ONE', 2);
define('STATE_START_OF_ROUND_TWO', 3);

define('STATE_ATTACK', 4);
define('STATE_SUPPORT', 5);
define('STATE_DEFENSE', 6);
define('STATE_DEFENSE_SUCCESS', 7);
define('STATE_DEFENSE_FAILURE', 8);

define('STATE_END_OF_TURN', 9);
define('STATE_NEXT_PLAYER', 10);
define('STATE_DRAW_CARDS', 11);

define('STATE_END_ROUND', 50);

define('STATE_BATTING', 51);
define('STATE_END_OF_BATTING', 53);

define('STATE_END_GAME', 99);

/*
 *  Card
 */
define('BLUE',      0);
define('RED',       1);
define('YELLOW',    2);