<?php
namespace BlazeBase\Cards;

use Blaze;
use BlazeBase\Game\Notifications;
use BlazeBase\Players\Players;

class Cards extends \BlazeBase\Singleton
{
    private static $deck = null;
    private static function getDeck()
    {
        if (is_null(self::$deck)) {
            self::$deck = self::getNew("module.common.deck");
            self::$deck->init("card");
            self::$deck->autoreshuffle = false;
        }
        return self::$deck;
    }
    
    public static function setupNewGame($players)
    {
        $cards = array();

        // 60장의 게임용 카드 설정
        // 1 ~ 10 숫자의 카드가 2장 씩
        for ($color = 0; $color < 3; $color++) {
            for ($value = (count($players) == 3 ? 2 : 1); $value <= 10; $value++) {
                $cards[] = array(
                    'type'      => $color,
                    'type_arg'  => $value,
                    'nbr'       => 2        // 생성할 카드 수
                );
            }
        }

        self::getDeck()->CreateCards($cards, 'deck');
        self::getDeck()->shuffle('deck');

        // 배팅 카드 설정
        $color = 0;
        // 플레이어 당 0vp 1장 , 1vp 2장 (player_number) * 3 장
        foreach ($players as $player_id => $player) {
            $cards = array();
            $cards[] = array('type' => $color, 'type_arg' => 0, 'nbr' => 1);
            $cards[] = array('type' => $color, 'type_arg' => 1, 'nbr' => 1);
            $cards[] = array('type' => $color, 'type_arg' => 1, 'nbr' => 1);
            $color++;
            self::getDeck()->CreateCards($cards, "betting_hand", $player_id);
        }

        // 트로피 카드 설정
        // 플레이어 수에 맞게 트로피 카드 생성 1라운드용 2라운드용 으로 나눔
        for ($round = 1; $round <= 2; $round++) {
            for ($value = $round; $value <= ($round == 1 ? count($players) - 1 : count($players)); $value++) {
                $cards = array();
                $cards[] = array(
                    'type'      => $round,
                    'type_arg'  => $value,
                    'nbr'       => 1
                );
                self::getDeck()->CreateCards($cards, 'trophy_deck_' . $round, $value);
            }
        };
    }

    private static function formatCard(Card $card) : array
    {
        return $card->getData();
    }

    private static function formatCards(array $cards) : array
    {
        return array_values(array_map(['BlazeBase\Cards\Cards', 'formatCard'], $cards));
    }

    private static function resToObject($row) : Card
    {
        $card = new Card($row['id'], $row['type'], $row['type_arg'], $row['location_arg']);
        return $card;
    }

    public static function toObjects($array) : array
    {
        $cards = array();
        foreach ($array as $row) $cards[] = self::resToObject($row);
        return $cards;
    }

    public static function getCountCards($location, $player_id = null)
    {
        if (is_null($player_id))
        {
            return self::getDeck()->countCardInLocation($location);
        } 
        else 
        {
            return self::getDeck()->countCardInLocation($location, $player_id);
        }
    }

    public static function getCard($id) : Card
    {
        return self::resToObject(self::getDeck()->GetCard($id));
    }

    public static function getCardsInLocation($location, $location_arg = null) : array
    {
        if (is_null($location_arg))
        {
            $cards = self::toObjects(self::getDeck()->getCardsInLocation($location));
            return self::formatCards($cards);
        } 
        else 
        {
            $cards = self::toObjects(self::getDeck()->getCardsInLocation($location, $location_arg));
            return self::formatCards($cards);
        }
    }

    public static function moveCard($id, $location, $arg = 0)
    {
        $card_id = ($id instanceof Card) ? $id->getId() : $id;
        self::getDeck()->moveCard($card_id, $location, $arg);
    }

    public static function moveCards($ids, $location, $arg = 0)
    {
        foreach ($ids as $card_id) self::moveCard($card_id, $location, $arg);
    }

    public static function moveAllCards($from_location, $to_location, $from_location_arg = null, $to_location_arg = 0)
    {
        self::getDeck()->moveAllCardsInLocation($from_location, $to_location, $from_location_arg, $to_location_arg);
    }

    
    // ----------------------------------------------------
    // ----------------- Play Card Action -----------------
    // ----------------------------------------------------

    public static function draw($count, $player_id)
    {
        if (self::getCountCards('deck') < $count)
        {
            self::moveAllCards('trumpCard', 'deck');
        }
        
        if (self::getCountCards('deck') <= 0)
        {
            return null;
        }
        else
        {
            $cards = self::toObjects(self::getDeck()->pickCards($count, 'deck', $player_id));
            return self::formatCards($cards);
        }
    }

    public static function drawTrumpCard() : Card
    {
        $card = self::resToObject(self::getDeck()->getCardOnTop('deck'));
        self::moveCard($card, 'trumpCard');
        return $card;
    }

    public static function roundSetting($round) : Card
    {
        $players = Players::getPlayers();

        if ($round == 1) 
        {
            foreach ($players as $player)
            {
                self::draw(5, $player->getId());
            }

            self::getDeck()->pickCardsForLocation(round(self::getCountCards('deck') / 2), 'deck', 'round_2_deck');
        }
        else if ($round == 2)
        {
            self::moveAllCards('attackedCards', 'deck');
            self::moveAllCards('attackCards',   'deck');
            self::moveAllCards('defenseCards',  'deck');

            self::moveAllCards('discard', 'deck');
            self::moveAllCards('hand', 'deck');
            
            self::getDeck()->shuffle('deck');
            
            foreach ($players as $player)
            {
                self::draw(5, $player->getId());
            }
            
            self::moveAllCards('deck', 'removeCards');
            self::moveAllCards('round_2_deck', 'deck');
        }

        $trump_card = self::drawTrumpCard();
        return $trump_card;
    }

    public static function moveAttackCards($cards)
    {
        $attack_cards_count = is_null(self::getCountCards('attackedCards')) ? 0 : self::getCountCards('attackedCards');
        foreach($cards as $card)
        {
            $attack_cards_count += 1;
            $card->setWeight($attack_cards_count);
            self::moveCard($card, 'attackCards', $attack_cards_count);
        }

        return $cards;
    }

    public static function moveDefenseCards($cards)
    {
        // 기존 공격 카드는 방어되었으므로 공격된 카드로 옳긴다.
        $attack_cards = self::getCardsInLocation('attackCards');
        foreach($attack_cards as $card)
        {
            self::moveCard($card['id'], 'attackedCards', $card['weight']);
        }

        foreach($cards as $card)
        {
            $card->setWeight(Cards::getCard($card->getWeight())->getWeight());
            self::moveCard($card, 'defenseCards', $card->getWeight());
        }

        return $cards;
    }

    public static function moveUsedCards($player_id, $is_defensed)
    {
        $player = Players::getPlayer($player_id);

        $attack_cards = self::getAttackCardsOnTable();
        $defense_cards = self::getCardsInLocation('defenseCards');

        if ($is_defensed == DEFENSE_SUCCESS)
        {
            self::moveAllCards('attackedCards', 'discard');
            self::moveAllCards('attackCards',   'discard');
            self::moveAllCards('defenseCards',  'discard');

            if (is_null($attack_cards) == false)
            {
                Blaze::get()->setGameStateValue('discardCardColor', $attack_cards[0]['color']);
                Blaze::get()->setGameStateValue('discardCardValue', $attack_cards[0]['value']);    
            }

            // Notifications
            Notifications::defenseSuccess($player, $defense_cards, $attack_cards);
        }
        else if($is_defensed == DEFENSE_FAILURE)
        {
            self::moveAllCards('attackedCards', 'hand', null, $player_id);
            self::moveAllCards('attackCards',   'hand', null, $player_id);
            self::moveAllCards('defenseCards',  'hand', null, $player_id);

            // Notifications
            Notifications::defenseFailure($player, $defense_cards, $attack_cards);
        }
    }

    public static function getAttackCardsOnTable() {
        $attackedCards = self::toObjects(self::getDeck()->getCardsInLocation('attackedCards'));
        $attackCards = self::toObjects(self::getDeck()->getCardsInLocation('attackCards'));
        return self::formatCards(array_merge($attackedCards, $attackCards));
    }

    public static function getDeckCardsCount() {
        $deck_count = self::getCountCards('deck');
        $trump_card_count = self::getCountCards('trumpCard');
        return ($deck_count + $trump_card_count);
    }

    // ------------------------------------------------------
    // ----------------- Trophy Card Action -----------------
    // ------------------------------------------------------
    public static function drawTrophyCard($round, $player_id) : Card
    {
        $card = self::resToObject(self::getDeck()->pickCardForLocation('trophy_deck_' . $round, 'trophy', $player_id));
        return $card;
    }

    // -------------------------------------------------------
    // ----------------- Betting Card Action -----------------
    // -------------------------------------------------------
    public static function bettingCard($card_id, $player_id)
    {
        $card = self::getCard($card_id);
        self::moveCard($card_id, 'betting', $player_id);
        return $card;
    }

    public static function resultBettingCard($last_player_id)
    {
        $betting_cards = self::getCardsInLocation('betting');
        $players = Players::getPlayers();
        foreach ($betting_cards as $card)
        {
            foreach($players as $player)
            {
                // 배팅 카드가 0이면 항상 다시 자기 손으로 돌아온다
                // 또는 베팅된 카드가 마지막 플레이어와 같으면 플레이어 점수에 등록한다.
                if ($card['value'] == 0)
                {
                    if ($player->getNo() == ((int)$card['color'] + 1))
                    {
                        self::moveCard($card['id'], 'betting_hand', $player->getId());
                    }
                }
                else if ($card['weight'] == $last_player_id)
                {
                    if ($player->getNo() == ((int)$card['color'] + 1))
                    {
                        self::moveCard($card['id'], 'betted', $player->getId());
                    }
                }
                else
                {
                    self::moveCard($card['id'], 'betted', $card['weight']);
                }
            }
        }
    }
}