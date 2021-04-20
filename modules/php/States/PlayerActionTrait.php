<?php
namespace BlazeBase\States;

use Blaze;
use BlazeBase\Cards\Cards;
use BlazeBase\Players\Players;

trait PlayerActionTrait
{
    // 1. 플레이할 플레이어가 카드가 없으면 바로 넘어간다.
    // 2. 공격카드 및 방어카드가 제한된 카드 수 이상일 경우 방어 성공으로 바로 넘어간다.
    public function stPlayerTurn()
    {
        // // ----- 1 -----
        // $active_player = Players::getActivePlayer();
        // $active_player_card_count = Cards::getCountCards('hand', $active_player->getId());

        // $limit_card_count       = Blaze::get()->getGameStateValue('limitCardCount');
        // if (is_null($limit_card_count) == false)
        // {
        //     $limit_card_count = $limit_card_count >= 5 ? 5 : $limit_card_count;
        // }

        // $attacked_cards_count   = Cards::getCountCards('attackedCards');
        // $attack_cards_count     = Cards::getCountCards('attackCards');
        // $defense_cares_count    = Cards::getCountCards('defenseCards');

        // if ($active_player_card_count <= 0)
        // {
        //     if ($active_player->getRole() == ROLE_ATTACKER || $active_player->getRole() == ROLE_SUPPORTER)
        //     {
        //         $this->pass();
        //         return;
        //     }
        //     else if ($active_player->getRole() == ROLE_DEFENDER)
        //     {
        //         Blaze::get()->setGameStateValue('isDefensed', DEFENSE_SUCCESS);

        //         // nextPlayer
        //         $this->gamestate->nextState('next');
        //         return;
        //     }
        // }

        // // ----- 2 -----
        // if ($attacked_cards_count + $attack_cards_count >= $limit_card_count)
        // {
        //     if ($defense_cares_count >= $limit_card_count)
        //     {
        //         Blaze::get()->setGameStateValue('isDefensed', DEFENSE_SUCCESS);

        //         // nextPlayer
        //         $this->gamestate->nextState('next');
        //         return;
        //     }
        // }
    }

    // 1. 다음 역할의 플레이어를 지정.
    // 2. 플레이어가 2명 남았을 때 지원자 생략
    public function stNextPlayer()
    {
        // ----- 1 -----
        $current_role_order = Blaze::get()->getGameStateValue('roleOrder');
        $next_role_order = Players::getNextRole($current_role_order);

        // ----- 2 -----
        $alive_player_count = Players::getAlivePlayerCount();
        if ($alive_player_count <= 2)
        {
            if ($next_role_order == ROLE_SUPPORTER)
            {
                $next_role_order = Players::getNextRole($next_role_order);
            }
        }

        Blaze::get()->setGameStateValue("roleOrder", $next_role_order);

        // endOfSubTurn
        $this->gamestate->nextState('start');
    }

    public function argPlayerTurn()
    {
        $trumpCardData = array(
            'color' => Blaze::get()->getGameStateValue('trumpCardColor'),
            'value' => Blaze::get()->getGameStateValue('trumpCardValue'),
        );

        return array(
            'attackCardOnTable'     => Cards::getAttackCardsOnTable(),
            'attackedCards'         => Cards::getCardsInLocation('attackedCards'),
            'defenseCards'          => Cards::getCardsInLocation('defenseCards'),
            'activePlayerRole'      => Players::getActivePlayer()->getRole(),
            'limitCardCount'        => Blaze::get()->getGameStateValue('limitCardCount'),
            'trumpCard'             => $trumpCardData
        );
    }

    public function attack($cards_id)
    {
        self::checkAction('attack');

        $active_player = Players::getActivePlayer();
        $attack_cards = $this->cardsFormat($cards_id);
        
        // logic check

        // result
        Blaze::get()->setGameStateValue('isAttacked', 1);
        $active_player->attack($attack_cards);

        // nextPlayer
        $this->gamestate->nextState('next');
    }

    public function defense($defense_cards_id, $attack_cards_id)
    {
        self::checkAction('defense');

        $active_player = Players::getActivePlayer();
        $defense_cards = array_map(function($defense_card_id, $attack_card_id) {
            $card = Cards::getCard($defense_card_id);
            $card->setWeight($attack_card_id);
            return $card;
        }, $defense_cards_id, $attack_cards_id);

        // logic check

        // result
        Blaze::get()->setGameStateValue('isAttacked', 0);
        $active_player->defense($defense_cards);

        // nextPlayer
        $this->gamestate->nextState('next');
    }

    public function support($cards_id)
    {
        self::checkAction('support');

        $active_player = Players::getActivePlayer();
        $support_cards = $this->cardsFormat($cards_id);

        // logic check

        // result
        Blaze::get()->setGameStateValue('isAttacked', 1);
        $active_player->attack($support_cards);

        // nextPlayer
        $this->gamestate->nextState('next');
    }

    public function pass()
    {
        self::checkAction('pass');
        
        $active_player = Players::getActivePlayer();
        $active_player_role = $active_player->getRole();

        if ($active_player_role == ROLE_ATTACKER)
        {
            $alive_player_count = Players::getAlivePlayerCount();

            if ($alive_player_count <= 2)
            {
                Blaze::get()->setGameStateValue('isDefensed', DEFENSE_SUCCESS);
            }
        }
        else if($active_player_role == ROLE_DEFENDER) 
        {
            Blaze::get()->setGameStateValue('isDefensed', DEFENSE_FAILURE);
        } 
        else if($active_player_role == ROLE_SUPPORTER)
        {
            $is_attacked = Blaze::get()->getGameStateValue('isAttacked');
            if ($is_attacked == 0)
            {
                Blaze::get()->setGameStateValue('isDefensed', DEFENSE_SUCCESS);
            }
        }

        // nextPlayer
        $this->gamestate->nextState('next');
    }

    private function cardsFormat($cards_id) : array
    {
        return array_map(function($id) {
            return Cards::getCard($id);
        }, $cards_id);
    }
}