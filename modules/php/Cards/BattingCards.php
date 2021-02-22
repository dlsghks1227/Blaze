<?php

namespace Blaze\Cards;

use Blaze\Cards\Card;
use Blaze\Players\Players;
use Blaze\Game\Notifications;

// 카드 상위 클래스를 만들어서 작업하는게 좋을꺼같다.

class BattingCards extends \APP_GameClass
{
    private static $battingDeck = null;

    private static function getBattingDeck()
    {
        if (is_null(self::$battingDeck)) {
            self::$battingDeck = self::getNew("module.common.deck");
            self::$battingDeck->init("battingCard");
            self::$battingDeck->autoreshuffle = false;
        }
        return self::$battingDeck;
    }

    public static function setupNewGame($players)
    {
        $color = 0;
        // 플레이어 당 0vp 1장 , 1vp 2장 (player_number) * 3 장
        foreach ($players as $player_id => $player) {
            $cards = array();
            $cards[] = array('type' => $color, 'type_arg' => 0, 'nbr' => 1);
            $cards[] = array('type' => $color, 'type_arg' => 1, 'nbr' => 1);
            $cards[] = array('type' => $color, 'type_arg' => 1, 'nbr' => 1);
            $color++;
            self::getBattingDeck()->CreateCards($cards, "hand", $player_id);
        }
    }

    public static function formatCard($card)
    {
        return $card->getData();
    }

    public static function formatCards($cards)
    {
        return array_values(array_map(['Blaze\Cards\BattingCards', 'formatCard'], $cards));
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
        self::getBattingDeck()->moveCard($id, $location, $arg);
    }

    public static function getCard($id)
    {
        return self::resToObject(self::getBattingDeck()->GetCard($id));
    }

    public static function getcountCardsInLocations()
    {
        return self::getBattingDeck()->countCardsInLocations();
    }

    public static function getHandCards() {
        $cards = self::toObjects(self::getBattingDeck()->getCardsInLocation("hand"));
        return self::formatCards($cards);
    }

    public static function getBettingCards()
    {
        $cards = self::toObjects(self::getBattingDeck()->getCardsInLocation("betting"));
        return self::formatCards($cards);
    }

    public static function getBettedCards()
    {
        $cards = self::toObjects(self::getBattingDeck()->getCardsInLocation("betted"));
        return self::formatCards($cards);
    }

    // 위치에 있는 카드의 숫자 반환
    public static function countCards($location, $player = null) {
        if (is_null($player)) {
            return self::getBattingDeck()->countCardsInLocation($location);
        } else {
            return self::getBattingDeck()->countCardsInLocation($location, $player);
        }
    }

    // $player_id의 hand에 있는 토큰들 정보 반환
    public static function getHand($player_id) {
        $cards = self::toObjects(self::getBattingDeck()->getCardsInLocation('hand', $player_id));
        return self::formatCards($cards);
    }
}