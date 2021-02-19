define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.cardTrait", null, {
        constructor: function() {
            this._notifications.push(
                ["drawCard", 1200]
            );
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
                if (playerId != this.player_id) {
                    this._attackCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'blaze-player-' + playerId);
                } else {
                    if ($('hand_item_' + card.id)) {
                        this._attackCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'hand_item_' + card.id);
                        this._playerHand.removeFromStockById(card.id);
                    }
                }
            });
        },

        placeDefenseCards: function(playerId, cards) {
            cards.forEach(card => {
                if (playerId != this.player_id) {
                    this._defenseCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'blaze-player-' + playerId);
                } else {
                    if ($('hand_item_' + card.id)) {
                        this._defenseCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'hand_item_' + card.id);
                        this._playerHand.removeFromStockById(card.id);
                    }
                }
            });
        },

        getCardUniqueId: function (color, value) {
            // 행 + 열 = 위치값
            return (color * 10) + (value - 1);
        },

        notif_drawCard(notif) {
            console.log("asdfasdfasdfdsf" + notif)
            for (var i in notif.args.cards) {
                var card = notif.args.cards[i];
                var color = card.type;
                var value = card.type_arg;
                this._playerHand.addToStockWithId(this.getCardUniqueId(color, value), card.id);
            }
        },
    });
});