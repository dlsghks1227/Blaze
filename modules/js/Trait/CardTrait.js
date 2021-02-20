define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.cardTrait", null, {
        constructor: function() {
            this._notifications.push(
                ["drawCard", 1200],
                ["defenseFailed", 2000]
            );
        },

        notif_drawCard: function(notif) {
            console.log(notif);
        },

        notif_defenseFailed: function(notif) {
            this.drawAttackCardsAndDefenseCards(notif.args.player_id, notif.args.attackCards, notif.args.defenseCards);
        },

        placeCard: function(posX, posY, color, value, isBack = false) {
            if ($('cardOnTable-' + posX + '-' + posY)) {
                dojo.destroy('cardOnTable-' + posX + '-' + posY);
            }

            dojo.place(this.format_block('jstpl_cardOnTable', {
                posX: posX,
                posY: posY,
                x: isBack ? 0 : this._cardWidth  * (value - 1),
                y: isBack ? 0 : this._cardHeight * (color),
                filp: isBack ? 'back' : 'front'
            }), 'table-container');
        },

        placeAttackCards: function(playerId, cards) {
            cards.forEach(card => {
                this._otherPlayerHand.get(playerId).removeFromStock(0);
                if (playerId != this.player_id) {
                    this._attackCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'blaze-player-' + playerId);
                } else {
                    if ($('hand-cards_item_' + card.id)) {
                        this._attackCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'hand-cards_item_' + card.id);
                        this._playerHand.removeFromStockById(card.id);
                    }
                }
            });
        },

        placeDefenseCards: function(playerId, cards) {
            cards.forEach(card => {
                this._otherPlayerHand.get(playerId).removeFromStock(0);
                if (playerId != this.player_id) {
                    this._defenseCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'blaze-player-' + playerId);
                } else {
                    if ($('hand-cards_item_' + card.id)) {
                        this._defenseCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'hand-cards_item_' + card.id);
                        this._playerHand.removeFromStockById(card.id);
                    }
                }
            });
        },

        drawCards: function(playerId, cards) {
            cards.forEach(card => {
                if (playerId != this.player_id) {
                }
            });
        },

        drawAttackCardsAndDefenseCards: function(playerId, attackCards, defenseCards) {
            attackCards.forEach(card => {
                if (playerId != this.player_id) {
                    if ($('attackCardOnTable_item_' + card.id)) {
                        this._otherPlayerHand.get(playerId).addToStock(0, 'attackCardOnTable_item_' + card.id);
                        this._attackCardPlace.removeFromStockById(card.id);
                    }
                } else {
                    if ($('attackCardOnTable_item_' + card.id)) {
                        this._playerHand.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'attackCardOnTable_item_' + card.id);
                        this._otherPlayerHand.get(playerId).addToStock(0);

                        this._attackCardPlace.removeFromStockById(card.id);
                    }
                }
            });

            defenseCards.forEach(card => {
                if (playerId != this.player_id) {
                    if ($('defenseCardOnTable_item_' + card.id)) {
                        this._otherPlayerHand.get(playerId).addToStock(0, 'defenseCardOnTable_item_' + card.id);
                        this._defenseCardPlace.removeFromStockById(card.id);
                    }
                } else {
                    if ($('defenseCardOnTable_item_' + card.id)) {
                        this._playerHand.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'defenseCardOnTable_item_' + card.id);
                        this._otherPlayerHand.get(playerId).addToStock(0);

                        this._defenseCardPlace.removeFromStockById(card.id);
                    }
                }
            });
        },

        getCardUniqueId: function (color, value) {
            // 행 + 열 = 위치값
            return (color * 10) + (value - 1);
        },
    });
});