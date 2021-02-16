<?php

/*
 *  State constants
 */
define('STATE_GAME_SETUP', 1);

define('STATE_START_OF_ROUND', 10);
define('STATE_END_OF_ROUND', 11);

define('STATE_START_OF_TURN', 20);
define('STATE_END_OF_TURN', 21);
define('STATE_NEXT_PLAYER', 22);
define('STATE_DRAW_CARDS', 23);

define('STATE_ATTACK', 30);
define('STATE_SUPPORT', 31);
define('STATE_DEFENSE', 32);
define('STATE_DEFENSE_SUCCESS', 33);
define('STATE_DEFENSE_FAILURE', 34);

define('STATE_BATTING', 50);
define('STATE_END_OF_BATTING', 51);

define('STATE_END_GAME', 99);

/*
 *  Card
 */
define('BLUE',      0);
define('RED',       1);
define('YELLOW',    2);