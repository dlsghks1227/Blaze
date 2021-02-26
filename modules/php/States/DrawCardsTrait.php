<?php
namespace BlazeBase\States;

use Blaze;
use BlazeBase\Cards\BattingCards;
use BlazeBase\Players\Players;
use BlazeBase\Cards\Cards;
use BlazeBase\Game\Notifications;

trait DrawCardsTrait
{
    public function stDrawCard() {
        $players = Players::getPlayers();
        $is_defensed = Blaze::get()->getGameStateValue("isDefensed");
        
        for ($i = ATTACKER; $i <= VOLUNTEER; $i++) {
            foreach ($players as $player) {
                if ($player->getRole() == $i) {
                    if ($i == DEFENDER) {
                        Cards::moveAttackedCards();
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
                    if ($deckCount <= 0) {
                        Cards::moveToTrumpSuitCard();
                    }
                    $deckCount = Cards::getDeckCount();
                    if ($deckCount > 0) {
                        $drawCount = 5 - Cards::countCards('hand', $player->getId());
                        if ($drawCount > $deckCount) {
                            Cards::moveToTrumpSuitCard();
                        }
                        if ($drawCount > 0 && ($i == ATTACKER || $i == VOLUNTEER || ($i == DEFENDER && $is_defensed == DEFENSE_SUCCESS)))
                            $player->drawCards($drawCount);
                    }
                }
            }
        }

        // stEndOfMainTurn
        $this->gamestate->nextState("start");
    }

    public function stBatting() {
        $this->gamestate->setAllPlayersMultiactive();
    }

    public function stEndOfBatting() {
        // 배팅 여부 활성화
        Blaze::get()->setGameStateValue("isBetting", 1 );

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
        $this->gamestate->setPlayerNonMultiactive($player_id, "end");
    }
}
