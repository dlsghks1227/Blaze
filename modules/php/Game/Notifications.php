<?php
namespace BlazeBase\Game;

use Blaze;
use BlazeBase\Players\Players;
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
        $draw_cards_count = is_null($draw_cards) == true ? 0 : count($draw_cards);

        self::notifyAll('draw', $message, array(
            'i18n'                  => array(),
            'player_name'           => $player->getName(),
            'player_id'             => $player->getId(),
            'draw_cards'            => $draw_cards,
            'draw_cards_count'      => $draw_cards_count,
            'deck_count'            => Cards::getCountCards('deck'),
            'player_cards_count'    => Cards::getCountCards('hand', $player->getId()),
        ));
    }

    public static function bettingPrivate($player, $selected_betting_card, $selected_player_id)
    {
        $message = clienttranslate('Bet ${betting_card_value} point on ${player_name}');

        $selected_player = Players::getPlayer($selected_player_id);

        self::notify($player->getId(), 'bettingPrivate', $message, array(
            'i18n'                          => array(),
            'player_name'                   => $selected_player->getName(),
            'player_id'                     => $player->getId(),
            'selected_player_id'            => $selected_player_id,
            'betting_card'                  => $selected_betting_card->getData(),
            'betting_card_value'            => $selected_betting_card->getValue(),
            'player_betting_cards_count'    => Cards::getCountCards('betting_hand', $player->getId()),
        ));
    }

    public static function endBetting()
    {
        $message = clienttranslate('End Betting!');

        self::notifyAll('endBetting', $message, array(
            'i18n'                  => array(),
            'players'               => Players::getDatas(),
            'betting_cards'         => Cards::getCardsInLocation('betting'),
        ));
    }

    public static function drawTrophyCard($player, $card)
    {
        $message = clienttranslate('${player_name} got ${point} trophy card.');

        self::notifyAll('drawTrophyCard', $message, array(
            'i18n'              => array(),
            'player_name'       => $player->getName(),
            'player_id'         => $player->getId(),
            'player_score'      => $player->getScore(),
            'point'             => $card->getValue(),
            'trophy_card'       => $card->getData(),
            'trophy_cards'      => Cards::getCardsInLocation('trophy'),
        ));
    }

    public static function startRoundPrivate($current_round)
    {
        $players = Players::getPlayers();

        $trump_card_data = array(
            'color' => Blaze::get()->getGameStateValue('trumpCardColor'),
            'value' => Blaze::get()->getGameStateValue('trumpCardValue'),
        );

        foreach ($players as $player)
        {
            $message = clienttranslate('');

            self::notify($player->getId(), 'startRoundPrivate', $message, array(
                'i18n'              => array(),
                'players'           => Players::getDatas($player->getId()),
                'nextPlayerTable'   => Blaze::get()->getNextPlayerTable(),

                'deck_count'        => Cards::getCountCards('deck'),
                'trump_card'        => $trump_card_data,
                'trophy_cards'      => Cards::getCardsInLocation('trophy_deck_' . $current_round),
            ));
        }
    }

    public static function endRound()
    {
        $message = clienttranslate('End round');

        self::notifyAll('endRound', $message, array(
            'i18n'                  => array(),
            'overall_betting_cards' => Cards::getCardsInLocation('betting'),
            'overall_betted_cards'  => Cards::getCardsInLocation('betted'),
            'overall_trophy_cards'  => Cards::getCardsInLocation('trophy'),
        ));
    }
}