define(["dojo", "dojo/_base/declare", "ebg/stock"], (dojo, declare) => {
    return declare("blaze.defenseCardsOnTable", null, {
        constructor: function() {
            this._notifications.push(
                ["defense", 1200],
                ["defenseSuccess", 1500],
                ["defenseFailure", 1500],
            );

            this._defenseCardStock = new ebg.stock();
        },

        /*
         * Notifications
         */
        notif_defense: function(notif) {
            const playerId            = notif.args.player_id;
            const defenseCards        = notif.args.defense_cards;
            const defenseCardsCount   = notif.args.defense_cards_count;
            const playerCardsCount    = notif.args.player_cards_count;

            defenseCards.forEach(card => {
                var uniqueId = this.getCardUniqueId(Number(card.color), Number(card.value));
                if (playerId == this.player_id) {
                    if ($("playerCardsStock_item_" + card.id)) {
                        this._defenseCardStock.addToStockWithId(uniqueId, card.id, ("playerCardsStock_item_" + card.id));
                        this._defenseCardStock.changeItemsWeight({[uniqueId]: card.weight});

                        this._playerCardStock.removeFromStockById(card.id);
                    }
                } else {
                    if ($("otherPlayerCards-" + playerId)) {
                        this._defenseCardStock.addToStockWithId(uniqueId, card.id, ("otherPlayerCards-" + playerId));
                        this._defenseCardStock.changeItemsWeight({[uniqueId]: card.weight});
                    }
                }
            });

            this.updateOtherPlayerPlayCardCount(playerId, playerCardsCount);
        },

        notif_defenseSuccess: function(notif) {
            const playerId          = notif.args.player_id;
            const defenseCards      = notif.args.defense_cards;
            const attackCards       = notif.args.attack_cards;
            const playerCardsCount  = notif.args.player_cards_count;

            this.updateOtherPlayerPlayCardCount(playerId, playerCardsCount);
        },

        notif_defenseFailure: function(notif) {
            const playerId          = notif.args.player_id;
            const defenseCards      = notif.args.defense_cards;
            const attackCards       = notif.args.attack_cards;
            const playerCardsCount  = notif.args.player_cards_count;

            this.updateOtherPlayerPlayCardCount(playerId, playerCardsCount);
        },

        /*
         *  Stock
         */
        createDefenseCardStock: function() {
            this._defenseCardStock.create(this, $("defenseCardStock"), this._CARD_WIDTH_L, this._CARD_HEIGHT_L);
            this._defenseCardStock.image_items_per_row = 10;
            this._defenseCardStock.centerItems = true;
            this._defenseCardStock.setOverlap(70, 0);
            this._defenseCardStock.setSelectionMode(0);
            this._defenseCardStock.extraClasses = "blazeCard";

            for (var color = 0; color <= 2; color++) {
                for (var value = 1; value <= 10; value++) {
                    var uniqueId = this.getCardUniqueId(color, value);
                    this._defenseCardStock.addItemType(uniqueId, uniqueId, g_gamethemeurl + "img/play_cards_L.png", uniqueId);
                }
            }
        },

        /*
         *  Setup
         */
        setupDefenseCards: function(defenseCards) {
            this.createDefenseCardStock();
            
            defenseCards.forEach(card => {
                var uniqueId = this.getCardUniqueId(Number(card.color), Number(card.value));
                this._defenseCardStock.addToStockWithId(uniqueId, card.id);
                this._defenseCardStock.changeItemsWeight({[uniqueId]: card.weight});
            });
        },
    });
});