<?php

namespace BlazeBase\States;

use Blaze;
use BlazeBase\Players\Players;
use BlazeBase\Cards\Cards;
use BlazeBase\Cards\TrophyCards;
use BlazeBase\Game\Log;
use BlazeBase\Game\Notifications;

trait PlayCardTrait
{   
    public function stNextPlayer() {
        $next_order = Blaze::get()->getGameStateValue("nextOrder");
        $order = Players::getNextRole($next_order);
        $is_defensed = Blaze::get()->getGameStateValue("isDefensed");
        $active_player = Players::getPlayer(self::getActivePlayerId());


        // 플레이어가 두명 밖에 남지 않으면 지원자 생략하고 넘어간다.
        $players = Players::getPlayers();
        $eliminated_player_count = 0;
        foreach ($players as $player) {
            if ($player->isEliminated() == false) {
                $eliminated_player_count++;
            }
        }

        if ($eliminated_player_count == 2) {
            if ($order == VOLUNTEER) {
                $order = Players::getNextRole($order);
            }
        }


        // 활성화된 플레이어의 카드가 없으면 트로피 카드 제공과 제외시킨다.
        // 활성화된 플레이어가 한명뿐이라면 트로피 카드 제공하지 않는다.        
        $deckCount = Cards::getDeckCount();
        $is_betting = Blaze::get()->getGameStateValue("isBetting");
        $count = Cards::countCards('hand', $active_player->getId());
        
        if ($deckCount <= 0 && 
            $count <= 0 &&
            $is_betting == 1 &&
            $eliminated_player_count >= 2 &&            // 남아 있는 플레이어가 2명 이상일 때 지급
            $active_player->isEliminated() == false) 
        {
            $trophy_card_id = Blaze::get()->getGameStateValue('trophyCardId');
            $active_player->eliminate(true);
            TrophyCards::moveCard($trophy_card_id, "hand", $active_player->getId());
            Notifications::getTrophyCard($active_player, $trophy_card_id);
            Blaze::get()->setGameStateValue('trophyCardId', $trophy_card_id - 1);
        }

        
        foreach ($players as $player) {
            if ($player->getRole() == $order) {
                $player_id = $player->getId();
                $this->gamestate->changeActivePlayer($player_id);
                self::giveExtraTime($player_id);
                break;
            }
        }

        Blaze::get()->setGameStateValue("nextOrder", $order);

        // stEndOfSubTurn
        $this->gamestate->nextState('next');
    }

    public function argPlayerTurn() {
        return array(
            'attackCard'            => Cards::getAttackCards(),
            'attackedCard'          => Cards::getAttackedCards(),
            'tableOnAttackCards'    => Cards::getAttackCards(),
            'DefenderCardsCount'    => Blaze::get()->getGameStateValue("limitCount"), 
            'activePlayerRole'      => Players::getPlayer(self::getActivePlayerId())->getRole(),
        );
    }

    public function attack($cards_id, $card_locations) {
        self::checkAction('attack');
        
        $cards = array_map(function($id, $location){
            $card = Cards::getCard($id);
            $card->setLocationArg($location);
            return $card;
        }, $cards_id, $card_locations);

        $player = Players::getPlayer(self::getActivePlayerId());
        Blaze::get()->setGameStateValue('isAttacked', 1 );

        $player->attack($cards);

        // stNextPlayer
        $this->gamestate->nextState('next');
    }

    public function defense($cards_id, $card_locations) {
        self::checkAction('defense');
        
        $cards = array_map(function($id, $location){
            $card = Cards::getCard($id);
            $card->setLocationArg($location);
            return $card;
        }, $cards_id, $card_locations);

        $player = Players::getPlayer(self::getActivePlayerId());
        Blaze::get()->setGameStateValue('isAttacked', 0 );
        Cards::moveAttackedCards();

        $player->defense($cards);

        // stNextPlayer
        $this->gamestate->nextState('next');
    }

    public function support($cards_id, $card_locations) {
        self::checkAction('support');

        $cards = array_map(function($id, $location){
            $card = Cards::getCard($id);
            $card->setLocationArg($location);
            return $card;
        }, $cards_id, $card_locations);

        $player = Players::getPlayer(self::getActivePlayerId());
        Blaze::get()->setGameStateValue('isAttacked', 1 );

        $player->attack($cards);

        // stNextPlayer
        $this->gamestate->nextState('next');
    }

    public function pass() {
        self::checkAction('pass');
        $player = Players::getActivePlayer();
        $player_role = $player->getRole();
        $is_attacked = Blaze::get()->getGameStateValue('isAttacked');

        if ($player_role == DEFENDER) {
            Cards::moveAttackedCards();
            Blaze::get()->setGameStateValue('limitCount', 5 );
            Blaze::get()->setGameStateValue('isDefensed', DEFENSE_FAILURE );
        } else if ($player_role == ATTACKER) {
            // 두 명밖에 남지 않았을 때 패스를 하면 DEFENSE_SUCCESS
            $players = Players::getPlayers();

            $eliminated_player_count = 0;
            foreach ($players as $player) {
                if ($player->isEliminated() == false) {
                    $eliminated_player_count++;
                }
            }
            
            if ($eliminated_player_count <= 2) {
                Blaze::get()->setGameStateValue('isDefensed', DEFENSE_SUCCESS );
            }
        } else if ($player_role == VOLUNTEER) {
            if ($is_attacked == 0) {
                Blaze::get()->setGameStateValue('isDefensed', DEFENSE_SUCCESS );
            }
        }

        // stNextPlayer
        $this->gamestate->nextState('next');
    }
}
