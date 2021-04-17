define(["dojo", "dojo/_base/declare", "ebg/stock"], (dojo, declare) => {
    return declare("blaze.trophyCards", null, {
        constructor: function() {
            this._notifications.push(
                ["drawTrophyCard", 1200],
            );

            this._trophyCardStock = new ebg.stock();
        },
        /*
         * Notifications
         */
        notif_drawTrophyCard: function(notif) {
            const playerId      = notif.args.player_id;
            const trophyCard    = notif.args.trophy_card;
            const trophyCards   = notif.args.trophy_cards;

            if ($("trophyCardsStock_item_" + trophyCard.id)) {
                const overallTrophyCardStock = this._overallTrophyCardStock.get(playerId);
                overallTrophyCardStock.addToStockWithId(trophyCard.value, trophyCard.id);

                this._trophyCardStock.removeFromStockById(trophyCard.id);
            }

            this.updateOverallTrophyCards(trophyCards);
        },

        /*
         *  Stock
         */
        createTrophyCardStock: function() {
            this._trophyCardStock.create(this, $("trophyCardsStock"), this._CARD_WIDTH_M, this._CARD_HEIGHT_M);
            this._trophyCardStock.image_items_per_row = 5;
            this._trophyCardStock.centerItems = true;
            this._trophyCardStock.setOverlap(50, 0);
            this._trophyCardStock.setSelectionMode(0);
            this._trophyCardStock.extraClasses = "blazeCard";

            for (var value = 1; value <= 5; value++) {
                this._trophyCardStock.addItemType(value, value, g_gamethemeurl + "img/trophy_cards_M.png", value - 1);
            }
        },
        
        /*
         *  Setup
         */
        setupTrophyCards: function(trophyCards) {
            this.createTrophyCardStock();
            
            trophyCards.forEach(card => {
                this._trophyCardStock.addToStockWithId(card.value, card.id);
            });
        },
    });
});