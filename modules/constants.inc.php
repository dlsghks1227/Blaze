<?php

/*
 *
 * Game States
 * 
 */
define('ST_GAME_SETUP',         1);
define('ST_PRE_END_GAME',       98);
define('ST_END_GAME',           99);

define('ST_START_OF_ROUND',     10);
define('ST_END_OF_ROUND',       11);

define('ST_START_OF_MAIN_TURN', 20);
define('ST_END_OF_MAIN_TURN',   21);

define('ST_START_OF_SUB_TURN',  30);
define('ST_END_OF_SUB_TURN',    31);

define('ST_PLAYER_TURN',        40);
define('ST_NEXT_PLAYER',        41);
define('ST_DRAW_CARD',          42);

define('ST_START_OF_BETTING',   50);
define('ST_END_OF_BETTING',     51);

/*
 *
 * Card Type
 * 
 */
define('BLUE',      0);
define('RED',       1);
define('YELLOW',    2);

/*
 *
 * Player Role
 * 
 */
define('ROLE_NONE',         0);
define('ROLE_ATTACKER',     1);
define('ROLE_DEFENDER',     2);
define('ROLE_SUPPORTER',    3);

/*
 *
 * Defense States
 * 
 */
define('DEFENSE_NONE',     0);
define('DEFENSE_SUCCESS',  1);
define('DEFENSE_FAILURE',  2);