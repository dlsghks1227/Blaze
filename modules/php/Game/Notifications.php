<?php
namespace Blaze\Game;

use Blaze\Cards\BattingCards;
use BlazeBananani;
use Blaze\Cards\Cards;
use Blaze\Cards\TrophyCards;
use Blaze\Players\Players;

class Notifications {
    protected static function notifyAll($name, $message, $data) {
        BlazeBananani::get()->notifyAllPlayers($name, $message, $data);
    }

    protected static function notify($pID, $name, $message, $data) {
        BlazeBananani::get()->notifyPlayer($pID, $name, $message, $data);
    }

    public static function drawCards($player, $cards) {
        $msg = clienttranslate('${player_name} draws ${amount} cards');
        $data = array(
            'i18n' => array(),
            'player_name' => $player->getName(),
            'player_id' => $player->getId(),
            "amount" => count($cards),
            "cards" => $cards,
            'deckCount' => Cards::getDeckCount(),
        );
        self::notifyAll('drawCard', $msg, $data);
    }

    public static function defeneseSuccess($player, $attack_cards, $defense_cards) {
        $msg = clienttranslate('${player_name} defensed succeed');
        $data = array(
            'i18n' => array(),
            'player_name' => $player->getName(),
            'player_id' => $player->getId(),
            'attackCards' => $attack_cards,
            'defenseCards' => $defense_cards,
        );
        self::notifyAll("defenseSuccess", $msg, $data);
    }

    public static function defenseFailed($player, $attack_cards, $defense_cards) {
        $msg = clienttranslate('${player_name} defensed failed');
        $data = array(
            'i18n' => array(),
            'player_name' => $player->getName(),
            'player_id' => $player->getId(),
            'attackCards' => $attack_cards,
            'defenseCards' => $defense_cards,
        );
        self::notifyAll("defenseFailed", $msg, $data);
    }

    public static function attack($player, $cards) {
        $msg = clienttranslate('${player_name} attacks with ${amount} cards');
        $cardsData = array_map(function($card){ return $card->getData(); }, $cards);
        self::notifyAll("attack", $msg, [
            'i18n' => array(),
            'player_name' => $player->getName(),
            'player_id' => $player->getId(),
            'amount' => count($cards),
            'cards' => $cardsData,
            'deckCount' => Cards::getDeckCount(),
        ]);
    }

    public static function defense($player, $cards) {
        $msg = clienttranslate('${player_name} defense with ${amount} cards');
        $cardsData = array_map(function($card){ return $card->getData(); }, $cards);
        self::notifyAll("defense", $msg, [
            'i18n' => array(),
            'player_name' => $player->getName(),
            'player_id' => $player->getId(),
            'amount' => count($cards),
            'cards' => $cardsData,
            'deckCount' => Cards::getDeckCount(),
        ]);
    }

    public static function pass($player) {
        $msg = clienttranslate('${player_name} passed');
        self::notifyAll("pass", $msg, array(
            'i18n' => array(),
            'player_name' => $player->getName(),
            'player_id' => $player->getId(),
            'deckCount' => Cards::getDeckCount(),
        ));
    }

    public static function betting($player, $selected_player, $selected_card) {
        $msg = clienttranslate('Bet ${select_point} point on ${player_name}');
        $data = array(
            'i18n'              => array(),
            'player_name'       => $selected_player->getName(),
            'player_id'         => $player->getId(),
            'player_no'         => $player->getNo(),
            'select_player_id'  => $selected_player->getId(),
            'select_card'       => $selected_card->getData(),
            'select_point'      => $selected_card->getValue(),
        );

        self::notify($player->getId(), 'betting', $msg, $data);
    }

    public static function endBetting($players, $bettingCards, $player_tokens) {
        $msg = clienttranslate('End Betting');
        $data = array(
            'i18n'              => array(),
            'players'           => $players,
            'betting_cards'     => $bettingCards,
            'player_tokens'     => $player_tokens,
        );

        self::notifyAll("endBetting", $msg, $data);
    }

    public static function updatePlayers() {
        $msg = clienttranslate('Change player role');
        $data = array(
            'i18n'      => array(),
            'deckCount' => Cards::getDeckCount(),
            'players'   => array_map(function($player) { return $player->getData(); }, Players::getPlayers()),
        );
        self::notifyAll("updatePlayer", $msg, $data);
    }

    public static function changeRole($player) {
        $msg = clienttranslate('${player_name} is the ${role_text}');
        $data = array(
            'i18n'          => array('role_text'),
            'player_name'   => $player->getName(),
            'player_id'     => $player->getId(),
            'role'          => $player->getRole(),
            'role_text'     => $player->getRoleFormat(),
            'deckCount'     => Cards::getDeckCount(),
        );

        self::notify($player->getId(), "changeRole", $msg, $data);
    }

    public static function getTrophyCard($player, $trophy_card_id) {
        $msg = clienttranslate('${player_name} got ${value} trophy card');
        $data = array(
            '118n'          => array(),
            'player_name'   => $player->getName(),
            'player_id'     => $player->getId(),
            'value'         => TrophyCards::getCard($trophy_card_id)->getValue(),
        );

        self::notifyAll("getTrophyCard", $msg, $data);
    }

    public static function roundStart($round) {
        $msg = clienttranslate('--------------- Round ${round} ---------------');
        $players = Players::getPlayers();
        $players_data = array_map(function($player){ return $player->getData(); }, $players);
        $data = array(
            'i18n'          => array(),
            'players'       => $players_data,
            'trumpSuitCard' => Cards::getTrumpSuitCard(),
            'deckCards'     => Cards::getAllCardsInDeck(),
            'tokenCards'    => BattingCards::getHandCards(),
            'bettingCards'  => BattingCards::getBettingCards(),
            'bettedCards'   => BattingCards::getBettedCards(),
            'trophyCards'           => TrophyCards::getDeckCards(),
            'trophyCardsOnPlayer'   => TrophyCards::getHandCards(),
            'round'         => $round
        );

        foreach ($players as $player) {
            $data[$player->getId() . '_hand'] = Cards::getHand($player->getId());
        }

        foreach ($players as $player) {
            $data[$player->getId() . '_token'] = BattingCards::getHand($player->getId());
        }

        self::notifyAll('roundStart', $msg, $data);
    }
}