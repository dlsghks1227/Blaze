<?php

namespace Blaze\Cards;

use Blaze\Cards\Card;
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

    public static function SetupNewGame($players_number)
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
    }

    public static function FormatCard($card)
    {
        return $card->format();
    }

    public static function FormatCards($cards)
    {
        return array_values(array_map(['Blaze\Cards\Cards', 'FormatCard'], $cards));
    }

    private static function ResToObject($row)
    {
        $card = new Card($row['id'], $row['type'], $row['type_arg']);
        return $card;
    }

    public static function ToObjects($array)
    {
        $cards = array();
        foreach ($array as $row) $cards[] = self::resToObject($row);
        return $cards;
    }

    public static function GetCard($id)
    {
        return self::ResToObject(self::GetDeck()->GetCard($id));
    }

    public static function GetAllCardsInDeck() {
        return self::GetDeck()->getCardsInLocation('deck');
    }

    public static function GetAllCardsInCurrentPlayer($current_player_id) {
        return self::GetDeck()->getCardsInLocation('hand', $current_player_id);
    }

    public static function GetCountCardsByLocationInPlayers() {
        return self::GetDeck()->countCardsByLocationArgs('hand');
    }

    // 위치에 있는 카드의 숫자 반환
    public static function CountCards($location, $player = null) {
        if (is_null($player)) {
            return self::GetDeck()->countCardsInLocation($location);
        } else {
            return self::GetDeck()->countCardsInLocation($location, $player);
        }
    }

    // 덱에 있는 카드의 숫자 반환
    public static function GetDeckCount() {
        return self::countCards('deck');
    }

    // $player_id의 hand에 있는 카드들(cards) 정보 반환
    public static function GetHand($player_id, $formatted = false) {
        $cards = self::ToObjects(self::GetDeck()->getCardsInLocation('hand', $player_id));
        return $formatted ? self::FormatCards($cards) : $cards;
    }

    public static function Draw($nbr, $player_id) {
        // 덱에 남아있는 카드가 있을 경우 드로우
        if (self::GetDeckCount() == 0) {
            return null;
        } else {
            $cards = self::GetDeck()->pickCards($nbr, 'deck', $player_id);
            return $cards;
        }
    }

    public static function MoveToTemplateDeck($nbr) {
        return self::GetDeck()->pickCardsForLocation($nbr, 'deck', 'tempDeck');
    }

    public static function BringFromTemplateDeck() {
        return self::GetDeck()->moveAllCardsInLocation('tempDeck', 'deck');
    }
}
