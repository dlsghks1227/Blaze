<?php
namespace BlazeBase\Game;

use Blaze;
use BlazeBase\Cards\Cards;

class Notifications {
    protected static function notifyAll($name, $message, $data) {
        Blaze::get()->notifyAllPlayers($name, $message, $data);
    }

    protected static function notify($player_id, $name, $message, $data) {
        Blaze::get()->notifyPlayer($player_id, $name, $message, $data);
    }

    public static function changeRole($players)
    {
        $message = clienttranslate('Change Role!');

        self::notifyAll('changeRole', $message, array(
            'i18n'          => array(),
            'players'       => $players,
            'deck_count'    => Cards::getCountCards('deck'),
        ));
    }

    public static function changeRolePrivate($player)
    {
        $message = clienttranslate('${player_name} is the ${player_role}.');

        self::notify($player->getId(), 'changeRolePrivate', $message, array(
            'i18n'          => array(),
            'player_name'   => $player->getName(),
            'player_id'     => $player->getId(),
            'player_role'   => $player->getRoleFormat()
        ));
    }

    public static function attack($player, $attack_cards)
    {
        $message = clienttranslate('${player_name} attacks with ${attack_cards_count} cards');

        $attack_cards_data = array_map(function($card) {
            return $card->getData();
        }, $attack_cards);

        self::notifyAll('attack', $message, array(
            'i18n'                  => array(),
            'player_name'           => $player->getName(),
            'player_id'             => $player->getId(),
            'attack_cards'          => $attack_cards_data,
            'attack_cards_count'    => count($attack_cards),
            'player_cards_count'    => Cards::getCountCards('hand', $player->getId()),
        ));
    }

    public static function defense($player, $defense_cards)
    {
        $message = clienttranslate('');

        $defense_cards_data = array_map(function($card) {
            return $card->getData();
        }, $defense_cards);
        
        self::notifyAll('defense', $message, array(
            'i18n'                  => array(),
            'player_name'           => $player->getName(),
            'player_id'             => $player->getId(),
            'defense_cards'         => $defense_cards_data,
            'defense_cards_count'   => count($defense_cards),
            'player_cards_count'    => Cards::getCountCards('hand', $player->getId()),
        ));
    }

    public static function defenseSuccess($player, $defense_cards, $attack_cards)
    {
        $message = clienttranslate('${player_name} defensed succeed');

        $discard_card_data = array(
            'color' => Blaze::get()->getGameStateValue('discardCardColor'),
            'value' => Blaze::get()->getGameStateValue('discardCardValue'),
        );

        self::notifyAll('defenseSuccess', $message, array(
            'i18n'                  => array(),
            'player_name'           => $player->getName(),
            'player_id'             => $player->getId(),
            'defense_cards'         => $defense_cards,
            'attack_cards'          => $attack_cards,
            'discard_card_data'     => $discard_card_data,
            'player_cards_count'    => Cards::getCountCards('hand', $player->getId()),
        ));
    }

    public static function defenseFailure($player, $defense_cards, $attack_cards)
    {
        $message = clienttranslate('${player_name} defense failed');

        self::notifyAll('defenseFailure', $message, array(
            'i18n'                  => array(),
            'player_name'           => $player->getName(),
            'player_id'             => $player->getId(),
            'defense_cards'         => $defense_cards,
            'attack_cards'          => $attack_cards,
            'player_cards_count'    => Cards::getCountCards('hand', $player->getId()),
        ));
    }

    public static function draw($player, $draw_cards)
    {
        $message = clienttranslate('');

        self::notifyAll('draw', $message, array(
            'i18n'                  => array(),
            'player_name'           => $player->getName(),
            'player_id'             => $player->getId(),
            'draw_cards'            => $draw_cards,
            'draw_cards_count'      => count($draw_cards),
            'deck_count'            => Cards::getCountCards('deck'),
            'player_cards_count'    => Cards::getCountCards('hand', $player->getId()),
        ));
    }
}