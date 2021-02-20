<?php
namespace Blaze\States;

use BlazeBananani;
use Blaze\Players\Players;
use Blaze\Cards\Cards;
use Blaze\Game\Notifications;

trait DrawCardsTrait
{
    public function stDrawCard() {
        $players = Players::getPlayers();
        $is_defensed = BlazeBananani::get()->getGameStateValue("isDefensed");
        $current_round = BlazeBananani::get()->getGameStateValue('round');

        if ($current_round == 2) {
            $this->gamestate->nextState("");
            return;
        }
        
        for ($i = ATTACKER; $i <= VOLUNTEER; $i++) {
            foreach ($players as $player) {
                if ($i == DEFENDER) {
                    if ($is_defensed == DEFENSE_FAILURE) {
                        $attack_cards = self::getAttackCards();
                        $defense_cards = self::getDefenseCards();
                        Cards::moveAttackAndDefenseCards($player->getId());
                        Notifications::defenseFailed($player, $attack_cards, $defense_cards);
                    } else {
                        Cards::discardAttackAndDefenseCards();
                    }
                }
                if ($player->getRole() == $i) {
                    $deckCount = Cards::getDeckCount();
                    if ($deckCount > 0) {
                        $count = Cards::countCards('hand', $player->getId());
                        $amount = ($deckCount >= 5) ? (5 - $count) : $deckCount;
                        if ($i == ATTACKER || $i == VOLUNTEER || ($i == DEFENDER && $is_defensed == DEFENSE_SUCCESS))
                            $player->drawCards($amount);
                    }
                }
            }
        }

        $this->gamestate->nextState("");
    }
}