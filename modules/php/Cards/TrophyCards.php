<?php

namespace Blaze\Cards;

use Blaze\Cards\Card;

class TrophyCards extends \APP_GameClass
{
    private static $trophyDeck = null;

    private static function getTrophyDeck()
    {
        if (is_null(self::$trophyDeck)) {
            self::$trophyDeck = self::getNew("module.common.deck");
            self::$trophyDeck->init("trophyCard");
            self::$trophyDeck->autoreshuffle = false;
        }
        return self::$trophyDeck;
    }

    public static function setupNewGame($player_number) {
        // 플레이어 수에 맞게 트로피 카드 생성 1라운드용 2라운드용 으로 나눔

        for ($round = 1; $round <= 2; $round++) {
            $cards = array();
            for ($value = $round; $value <= ($round == 1 ? $player_number - 1 : $player_number); $value++) {
                $cards[] = array(
                    'type'      => $round,
                    'type_arg'  => $value,
                    'nbr'       => 1
                );
           }
           self::getTrophyDeck()->CreateCards($cards, 'deck', $round);
        };
    }

    public static function formatCard($card)
    {
        return $card->getData();
    }

    public static function formatCards($cards)
    {
        return array_values(array_map(['Blaze\Cards\TrophyCards', 'formatCard'], $cards));
    }

    public static function resToObject($row)
    {
        $card = new Card($row['id'], $row['type'], $row['type_arg'], $row['location_arg']);
        return $card;
    }

    public static function toObjects($array)
    {
        $cards = array();
        foreach ($array as $row) $cards[] = self::resToObject($row);
        return $cards;
    }

    public static function moveCard($mixed, $location, $arg = 0) {
        $id = ($mixed instanceof Card) ? $mixed->getId() : $mixed;
        self::getTrophyDeck()->moveCard($id, $location, $arg);
    }

    public static function getCard($id)
    {
        return self::resToObject(self::getTrophyDeck()->GetCard($id));
    }

    public static function getcountCardsInLocations()
    {
        return self::getTrophyDeck()->countCardsInLocations();
    }

    public static function getDeckCards() {
        $cards = self::toObjects(self::getTrophyDeck()->getCardsInLocation("deck"));
        return self::formatCards($cards);
    }

    public static function getHandCards() {
        $cards = self::toObjects(self::getTrophyDeck()->getCardsInLocation("hand"));
        return self::formatCards($cards);
    }

    // 위치에 있는 카드의 숫자 반환
    public static function countCards($location, $player = null) {
        if (is_null($player)) {
            return self::getTrophyDeck()->countCardsInLocation($location);
        } else {
            return self::getTrophyDeck()->countCardsInLocation($location, $player);
        }
    }

    // $player_id의 hand에 있는 토큰들 정보 반환
    public static function getHand($player_id) {
        $cards = self::toObjects(self::getTrophyDeck()->getCardsInLocation('hand', $player_id));
        return self::formatCards($cards);
    }
}