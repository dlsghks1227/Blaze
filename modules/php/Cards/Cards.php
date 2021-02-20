<?php

namespace Blaze\Cards;

use Blaze\Cards\Card;
use Blaze\Players\Players;
use Blaze\Game\Log;
use Blaze\Game\Notifications;

class Cards extends \APP_GameClass
{
    private static $deck = null;

    private static function getDeck()
    {
        if (is_null(self::$deck)) {
            self::$deck = self::getNew("module.common.deck");
            self::$deck->init("card");
            self::$deck->autoreshuffle = true;
        }
        return self::$deck;
    }

    public static function setupNewGame($players_number)
    {
        $cards = array();
        // 60장의 게임용 카드
        // 1 ~ 10 숫자의 카드가 2장 씩
        for ($color = 0; $color < 3; $color++) {
            for ($value = ($players_number == 3 ? 2 : 1); $value <= 10; $value++) {
                $cards[] = array(
                    'type'      => $color,
                    'type_arg'  => $value,
                    'nbr'       => 2        // 생성할 카드 수
                );
            }
        }

        self::GetDeck()->CreateCards($cards, 'deck');
        self::GetDeck()->shuffle('deck');
        self::GetDeck()->autoreshuffle = false;
    }

    public static function formatCard($card)
    {
        return $card->getData();
    }

    public static function formatCards($cards)
    {
        return array_values(array_map(['Blaze\Cards\Cards', 'formatCard'], $cards));
    }

    private static function resToObject($row)
    {
        $card = new Card($row['id'], $row['type'], $row['type_arg']);
        return $card;
    }

    public static function toObjects($array)
    {
        $cards = array();
        foreach ($array as $row) $cards[] = self::resToObject($row);
        return $cards;
    }

    public static function getCard($id)
    {
        return self::resToObject(self::getDeck()->GetCard($id));
    }

    public static function getAllCardsInDeck() {
        $cards = self::toObjects(self::getDeck()->getCardsInLocation('deck'));
        return $cards;
    }

    public static function getAllCardsInCurrentPlayer($current_player_id) {
        return self::getDeck()->getCardsInLocation('hand', $current_player_id);
    }

    public static function getCountCardsByLocationInPlayers() {
        return self::getDeck()->countCardsByLocationArgs('hand');
    }

    public static function moveCard($mixed, $location, $arg = 0) {
        $id = ($mixed instanceof Card) ? $mixed->getId() : $mixed;
        self::getDeck()->moveCard($id, $location, $arg);
    }

    public static function moveAttackCards($cards) {
        foreach ($cards as $card) self::moveCard($card, 'attackCards');
    }

    public static function moveDefenseCards($cards) {
        foreach ($cards as $card) self::moveCard($card, 'defenseCards');
    }

    public static function moveAttackAndDefenseCards($player_id) {
        self::getDeck()->moveAllCardsInLocation('attackCards', 'hand', null, $player_id);
        self::getDeck()->moveAllCardsInLocation('defenseCards', 'hand', null, $player_id);
    }

    public static function discardAttackAndDefenseCards() {
        self::getDeck()->moveAllCardsInLocation('attackCards', 'discard');
        self::getDeck()->moveAllCardsInLocation('defenseCards', 'discard');
    }

    // 위치에 있는 카드의 숫자 반환
    public static function countCards($location, $player = null) {
        if (is_null($player)) {
            return self::getDeck()->countCardsInLocation($location);
        } else {
            return self::getDeck()->countCardsInLocation($location, $player);
        }
    }

    // 덱에 있는 카드의 숫자 반환
    public static function getDeckCount() {
        return self::countCards('deck');
    }

    // $player_id의 hand에 있는 카드들(cards) 정보 반환
    public static function getHand($player_id) {
        $cards = self::toObjects(self::getDeck()->getCardsInLocation('hand', $player_id));
        return self::formatCards($cards);
    }

    public static function getTrumpSuitCard() {
        $card = self::toObjects(self::getDeck()->getCardsInLocation('trumpSuitCard'));
        return self::formatCards($card)[0];
    }

    public static function getAttackCards() {
        $cards = self::toObjects(self::getDeck()->getCardsInLocation('attackCards'));
        return self::formatCards($cards);
    }

    public static function getDefenseCards() {
        $cards = self::toObjects(self::getDeck()->getCardsInLocation('defenseCards'));
        return self::formatCards($cards);
    }

    public static function draw($nbr, $player_id) {
        // 덱에 남아있는 카드가 있을 경우 드로우
        if (self::getDeckCount() < 0) {
            return null;
        } else {
            $cards = self::toObjects(self::getDeck()->pickCards($nbr, 'deck', $player_id));
            return self::formatCards($cards);
        }
    }

    public static function drawTrumpSuitCard() {
        $card = self::resToObject(self::getDeck()->getCardOnTop('deck'));
        self::getDeck()->moveCard($card->getId(), 'trumpSuitCard');
        return $card;
    }

    public static function moveToTemplateDeck($nbr) {
        return self::GetDeck()->pickCardsForLocation($nbr, 'deck', 'tempDeck');
    }

    public static function bringFromTemplateDeck() {
        return self::GetDeck()->moveAllCardsInLocation('tempDeck', 'deck');
    }
}
