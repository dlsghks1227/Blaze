define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.cardTrait", null, {
        constructor: function() {
            this._notifications.push(
                ["draw", 1500],
            );
        },
        /*
         * Notifications
         */
        notif_draw: function(notif) {
            const playerId          = notif.args.player_id;
            const drawCards         = notif.args.draw_cards;
            const drawCardsCount    = notif.args.draw_cards_count;
            const deckCount         = notif.args.deck_count;
            const playerCardsCount  = notif.args.player_cards_count;

            if (playerId == this.player_id) {
                if (drawCards != null) {
                    drawCards.forEach(card => {
                        var uniqueId = this.getCardUniqueId(Number(card.color), Number(card.value));
                        this._playerCardStock.addToStockWithId(uniqueId, card.id, "deck");
                    });
                }
            } else {
                for (var count = 0; count < Number(drawCardsCount); count++) {
                    this.slideTemporaryObject('<div id="drawCard" class="blazeCard"></div>', "deck", "drawCard", "otherPlayerCards-" + playerId, 500, count * 100).play();
                }
            }

            this.updatePlayerCardStockOverlap();

            this.updateDeckCount(deckCount);
            this.updateOtherPlayerPlayCardCount(playerId, playerCardsCount);
        },

        /*
         *  Card update
         */
        updateDeckCount: function(count) {
            this.removeDeck();
            this.updateDeckText(_("Deck"));
            this._TRUMP_CARD_OPACITY = 1.0;
            
            if (Number(count) > 0) {
                dojo.place(this.format_block("jstpl_deck", {
                    count: count,
                }), "playCardOnTable");  
            } else {
                // 카드 수가 0 일 떄 트럼프 카드 투명 및 글씨표기
                this.updateDeckText(_("Trump Card"));
                this._TRUMP_CARD_OPACITY = 0.5;
                this.updateTrumpCard(this._TRUMP_CARD_VALUE, this._TRUMP_CARD_COLOR);
            }
        },

        updateDeckText: function(text) {
            if ($("playCardText")) {
                dojo.destroy("playCardText");
            }

            dojo.place(this.format_block("jstpl_deckText", {
                text: text
            }), "table");
        },

        updateTrumpCard: function(color, value) {
            if ($("trump")) {
                dojo.destroy("trump");
            }

            dojo.place(this.format_block("jstpl_trump", {
                x: this._CARD_WIDTH_L   * (value - 1),
                y: this._CARD_HEIGHT_L  * color,
                opacity: this._TRUMP_CARD_OPACITY,
            }), "playCardOnTable");
        },

        updateDiscardCard: function(card) {
            if (card.value >= 0) {
                if ($("discard")) {
                    dojo.destroy("discard");
                }
    
                dojo.place(this.format_block("jstpl_discard", {
                    x: this._CARD_WIDTH_L   * (card.value - 1),
                    y: this._CARD_HEIGHT_L  * card.color,
                }), "discardCardOnTable");
            } else {
                this.removeDiscardCard();
            }
        },

        removeDeck: function() {
            if ($("deck")) {
                dojo.destroy("deck");
            }
        },

        removeDiscardCard: function() {
            if ($("discard")) {
                dojo.destroy("discard");
            }
        },

        /*
         *  Utility
         */
        getCardUniqueId: function(color, value) {
            return (Number(color) * 10) + (Number(value) - 1);
        },

        getCardDataWithType: function(cardType) {
            return {
                color: Math.floor((Number(cardType) % 100) / 10),
                value: (Number(cardType) % 10) + 1
            }
        },

        getBettingCardUniqueId: function(color, value) {
          return (Number(color) * 2) + Number(value); 
        },
    });
});