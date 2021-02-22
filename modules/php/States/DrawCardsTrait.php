<?php
namespace Blaze\States;

use Blaze\Cards\BattingCards;
use BlazeBananani;
use Blaze\Players\Players;
use Blaze\Cards\Cards;
use Blaze\Game\Notifications;

trait DrawCardsTrait
{
    public function stDrawCard() {
        $players = Players::getPlayers();
        $is_defensed = BlazeBananani::get()->getGameStateValue("isDefensed");
        
        for ($i = ATTACKER; $i <= VOLUNTEER; $i++) {
            foreach ($players as $player) {
                if ($player->getRole() == $i) {
                    if ($i == DEFENDER) {
                        $attack_cards = Cards::getAttackCards();
                        $defense_cards = Cards::getDefenseCards();
                        if ($is_defensed == DEFENSE_FAILURE) {
                            Cards::moveAttackAndDefenseCards($player->getId());
                            Notifications::defenseFailed($player, $attack_cards, $defense_cards);
                        } else {
                            Cards::discardAttackAndDefenseCards();
                            Notifications::defeneseSuccess($player, $attack_cards, $defense_cards);
                        }
                    }

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

    public function stBatting() {
        $this->gamestate->setAllPlayersMultiactive();
    }

    public function stEndOfBatting() {
        // 배팅 여부 활성화
        BlazeBananani::get()->setGameStateValue("isBetting", 1 );

        // 배팅 종료 알리기
        Notifications::endBetting(
            Players::getData(self::getCurrentPlayerId()),
            BattingCards::getBettingCards(),
            BattingCards::getHandCards()
        );
        
        // stStartOfMainTurn
        $this->gamestate->nextState("");
    }

    public function batting($card_id, $selected_player_id) {
        self::checkAction('batting');
        $player_id = self::getCurrentPlayerId();

        // logic
        BattingCards::moveCard($card_id, "betting", $selected_player_id);
        Notifications::betting(
            Players::getPlayer($player_id),
            Players::getPlayer($selected_player_id),
            BattingCards::getCard($card_id)
        );

        // stEndOfBatting
        $this->gamestate->setPlayerNonMultiactive($player_id, "");
    }
}