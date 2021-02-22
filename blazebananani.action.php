<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * BlazeBananani implementation : © <Inhwan Lee> <dlsghks1227@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * blazebananani.action.php
 *
 * BlazeBananani main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/blazebananani/blazebananani/myAction.html", ...)
 *
 */


class action_blazebananani extends APP_GameAction
{
	// Constructor: please do not modify
	public function __default()
	{
		if (self::isArg('notifwindow')) {
			$this->view = "common_notifwindow";
			$this->viewArgs['table'] = self::getArg("table", AT_posint, true);
		} else {
			$this->view = "blazebananani_blazebananani";
			self::trace("Complete reinitialization of board game");
		}
	}

	// TODO: defines your action entry points there
	public function attack()
	{
		self::setAjaxMode();
		$cards = explode(";", self::getArg("cards", AT_numberlist, false));
		$result = $this->game->attack($cards);
		self::ajaxResponse();
	}

	public function defense()
	{
		self::setAjaxMode();
		$cards = explode(";", self::getArg("cards", AT_numberlist, false));
		$result = $this->game->defense($cards);
		self::ajaxResponse();
	}

	public function support()
	{
		self::setAjaxMode();
		$cards = explode(";", self::getArg("cards", AT_numberlist, false));
		$result = $this->game->support($cards);
		self::ajaxResponse();
	}

	public function pass()
	{
		self::setAjaxMode();
		$result = $this->game->pass();
		self::ajaxResponse();
	}

	public function batting()
	{
		self::setAjaxMode();
		$card_id = self::getArg("card_id", AT_posint, true);
		$player_id = self::getArg("player_id", AT_posint, true);
		$result = $this->game->batting($card_id, $player_id);
		self::ajaxResponse();
	}
}