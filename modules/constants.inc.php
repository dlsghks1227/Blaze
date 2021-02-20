<?php

/*
 *  State constants
 */
define('ST_GAME_SETUP', 1);

define('ST_START_OF_ROUND', 10);
define('ST_END_OF_ROUND', 11);

define('ST_START_OF_MAIN_TURN', 20);
define('ST_END_OF_MAIN_TURN', 21);
define('ST_START_OF_SUB_TURN', 22);
define('ST_END_OF_SUB_TURN', 23);

define('ST_PLAYER_TURN', 30);
define('ST_NEXT_PLAYER', 31);
define('ST_DRAW_CARD', 32);

define('ST_BATTING', 40);
define('ST_END_OF_BATTING', 41);

define('ST_END_GAME', 99);

/*
 *  Card
 */
define('BLUE',      0);
define('RED',       1);
define('YELLOW',    2);

/*
 *  Role
 */
define('ATTACKER',  1);
define('DEFENDER',  2);
define('VOLUNTEER', 3);

/*
 *  Defense state
 */
define('DEFENSE_SUCCESS',  1);
define('DEFENSE_FAILURE',  2);