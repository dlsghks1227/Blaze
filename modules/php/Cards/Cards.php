<?php

namespace Blaze\Cards;

use Blaze\Game\Log;

class Cards extends \APP_GameClass
{
    private static $deck = null;

    private static function GetDeck()
    {
        if (is_null(self::$deck)) {
            self::$deck = self::getNew("module.common.deck");
            self::$deck->init("card");
            self::$deck->autoreshuffle = true;
        }
        return self::$deck;
    }

    public static function SetupNewGame()
    {
        $cards = array();
        // 60장의 게임용 카드
        // 1 ~ 10 숫자의 카드가 2장 씩
        for ($color = 0; $color < 3; $color++) {
            for ($value = 1; $value <= 10; $value++) {
                $cards[] = array(
                    'type'      => $color,
                    'type_arg'  => $value,
                    'nbr'       => 2        // 생성할 카드 수
                );
            }
        }

        self::GetDeck()->CreateCards($cards, 'deck');
        self::GetDeck()->shuffle('deck');
    }

    public static function FormatCard($card)
    {
        return $card->format();
    }

    public static function FormatCards($cards)
    {
        return array_values(array_map(['Blaze\Cards\Cards', 'FormatCard'], $cards));
    }

    // public static function GetCard($id)
    // {
    //     return self::resToObject(self::GetDeck()->getCard($id));
    // }

    // public static function GetCurrentCard()
    // {
    //     return self::GetCard(Log::GetCurrentCard());
    // }

    // private static function resToObject($row)
    // {
    //     $card_id = $row['type'];
    //     $name = "Blarz"
    // }

    // public static function toObjects($array)
    // {
    //     $cards = array();
    //     foreach ($array as $row) {
    //         $cards[] = self::resToObject($row);
    //     }
    //     return $cards;
    // }

    public static function Draw($nbr, $playerId)
    {
        // 덱에 남아있는 카드가 있을 경우 드로우
        if (self::GetDeckCount() == 0) {
            // 2페이즈 전환
            return null;
        } else {
            $cards = self::GetDeck()->pickCards($nbr, 'deck', $playerId);
            return $cards;
        }
    }

    public static function GetAllCardsInDeck()
    {
        return self::GetDeck()->getCardsInLocation('deck');
    }


    // 위치에 있는 카드의 숫자 반환
    public static function CountCards($location, $player = null)
    {
        if (is_null($player)) {
            return self::GetDeck()->countCardsInLocation($location);
        } else {
            return self::GetDeck()->countCardsInLocation($location, $player);
        }
    }

    // 덱에 있는 카드의 숫자 반환
    public static function GetDeckCount()
    {
        return self::countCards('deck');
    }


    public static function GetHand($playerId, $formatted = false)
    {
        $cards = self::GetDeck()->getCardsInLocation('hand', $playerId);
        return $cards;
    }
}
