define(["dojo", "dojo/_base/declare", "ebg/stock"], (dojo, declare) => {
    return declare("blaze.attackCardsOnTable", null, {
        constructor: function() {
            this._notifications.push(
                ["attack", 1700],
            );

            this._attackCardStock = new ebg.stock();
        },

        /*
         * Notifications
         */
        notif_attack: function(notif) {
            const playerId            = notif.args.player_id;
            const attackCards         = notif.args.attack_cards;
            const attackCardsCount    = notif.args.attack_cards_count;
            const playerCardsCount    = notif.args.player_cards_count;
            
            const offset = 100;
            const uniqueIds = [];
            const attackStockItems = this._attackCardStock.getAllItems();
            attackStockItems.forEach(card => {
                uniqueIds.push(card.type)
            });

            attackCards.forEach(card => {
                var uniqueId = this.getCardUniqueId(Number(card.color), Number(card.value));

                if (playerId == this.player_id) {
                    if ($("playerCardsStock_item_" + card.id)) {
                        if (uniqueIds.includes(uniqueId) == true) {
                            this._attackCardStock.addItemType(uniqueId + offset, uniqueId + offset, g_gamethemeurl + "img/play_cards_L.png", uniqueId);
                            this._attackCardStock.addToStockWithId(uniqueId + offset, card.id, ("playerCardsStock_item_" + card.id));
                            this._attackCardStock.changeItemsWeight({[uniqueId + offset]: card.weight});
                        } else {
                            this._attackCardStock.addToStockWithId(uniqueId, card.id, ("playerCardsStock_item_" + card.id));
                            this._attackCardStock.changeItemsWeight({[uniqueId]: card.weight});
                            uniqueIds.push(uniqueId);
                        }
                        
                        this._playerCardStock.removeFromStockById(card.id);
                    }
                } else {
                    if ($("otherPlayerCards-" + playerId)) {
                        if (uniqueIds.includes(uniqueId) == true) {
                            this._attackCardStock.addItemType(uniqueId + offset, uniqueId + offset, g_gamethemeurl + "img/play_cards_L.png", uniqueId);
                            this._attackCardStock.addToStockWithId(uniqueId + offset, card.id, ("otherPlayerCards-" + playerId));
                            this._attackCardStock.changeItemsWeight({[uniqueId + offset]: card.weight});
    
                        } else {
                            this._attackCardStock.addToStockWithId(uniqueId, card.id, ("otherPlayerCards-" + playerId));
                            this._attackCardStock.changeItemsWeight({[uniqueId]: card.weight});
                            uniqueIds.push(uniqueId);
                        }
                    }
                }
            });
            
            this.updatePlayerCardStockOverlap();
            this.updateOtherPlayerPlayCardCount(playerId, playerCardsCount);
        },

        /*
         *  Stock
         */
        createAttackCardStock: function() {
            this._attackCardStock.create(this, $("attackCardStock"), this._CARD_WIDTH_L, this._CARD_HEIGHT_L);
            this._attackCardStock.image_items_per_row = 10;
            this._attackCardStock.setOverlap(70, 0);
            this._attackCardStock.setSelectionMode(0);
            this._attackCardStock.setSelectionAppearance("class");
            this._attackCardStock.extraClasses = "blazeCard";

            for (var color = 0; color <= 2; color++) {
                for (var value = 1; value <= 10; value++) {
                    var uniqueId = this.getCardUniqueId(color, value);
                    this._attackCardStock.addItemType(uniqueId, uniqueId, g_gamethemeurl + "img/play_cards_L.png", uniqueId);
                }
            }

            dojo.connect(this._attackCardStock, "onChangeSelection", this, "onAttackCardSelectionChanged");
        },

        /*
         *  Setup
         */
        setupAttackCards: function(attackCards) {
            this.createAttackCardStock();
            
            const offset = 100;
            const uniqueIds = [];
            attackCards.forEach(card => {
                var uniqueId = this.getCardUniqueId(Number(card.color), Number(card.value));
                if (uniqueIds.includes(uniqueId) == true) {
                    this._attackCardStock.addItemType(uniqueId + offset, uniqueId + offset, g_gamethemeurl + "img/play_cards_L.png", uniqueId);
                    this._attackCardStock.addToStockWithId(uniqueId + offset, card.id);
                    this._attackCardStock.changeItemsWeight({[uniqueId + offset]: card.weight});
                } else {
                    this._attackCardStock.addToStockWithId(uniqueId, card.id);
                    this._attackCardStock.changeItemsWeight({[uniqueId]: card.weight});
                    uniqueIds.push(uniqueId);
                }
            });
        },
    });
});