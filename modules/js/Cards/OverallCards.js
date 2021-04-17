define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.overallCards", null, {
        constructor: function() {
            this._notifications.push(
                // ["", 1200],
            );

            this._overallBettingCardStock = new Map();
            this._overallBettedCardStock  = new Map();
            this._overallTrophyCardStock  = new Map();
        },
        
        /*
         *  Stock
         */
        createOverallBettingCardStock: function(playerId) {
            const stock = new ebg.stock();
            stock.create(this, $("overallBettingCardStock-" + playerId), this._CARD_WIDTH_S, this._CARD_HEIGHT_S);
            stock.image_items_per_row = 5;
            stock.setOverlap(50, 0);
            stock.setSelectionMode(0);
            stock.extraClasses = "blazeCard";

            for (var color = 0; color <= 4; color++) {
                stock.addItemType(color, color, g_gamethemeurl + "img/betting_cards_back_S.png", color);
            }

            this._overallBettingCardStock.set(playerId, stock);
        },

        createOverallBettedCardStock: function(playerId) {
            const stock = new ebg.stock();
            stock.create(this,$("overallBettedCardStock-" + playerId), this._CARD_WIDTH_S, this._CARD_HEIGHT_S);
            stock.image_items_per_row = 2;
            stock.setOverlap(50, 0);
            stock.setSelectionMode(0);
            stock.extraClasses = "blazeCard";

            for (var color = 0; color <= 4; color++) {
                for (var value = 0; value <= 1; value++) {
                    var uniqueId = this.getBettingCardUniqueId(color, value);
                    stock.addItemType(uniqueId, uniqueId, g_gamethemeurl + "img/betting_cards_S.png", uniqueId);
                }        
            }
            
            this._overallBettedCardStock.set(playerId, stock);
        },

        createOverallTrophyCardStock: function(playerId) {
            const stock = new ebg.stock();
            stock.create(this, $("overallTrophyCardStock-" + playerId), this._CARD_WIDTH_S, this._CARD_HEIGHT_S);
            stock.image_items_per_row = 5;
            stock.setOverlap(50, 0);
            stock.setSelectionMode(0);
            stock.extraClasses = "blazeCard";

            for (var value = 1; value <= 5; value++) {
                stock.addItemType(value, value, g_gamethemeurl + "img/trophy_cards_S.png", value - 1);
            }
            
            this._overallTrophyCardStock.set(playerId, stock);
        },
        /*
         *  Setup player
         */

        setupOverallCardStock: function(playerId) {
            this.placeOverall(playerId);

            this.createOverallBettingCardStock(playerId);
            this.createOverallBettedCardStock(playerId);
            this.createOverallTrophyCardStock(playerId);
        },

        updateOverallCards: function(bettingCards, bettedCards, trophyCards) {
            this.updateOverallBettingCards(bettingCards);
            this.updateOverallBettedCards(bettedCards);
            this.updateOverallTrophyCards(trophyCards);
        },

        /*
         *  overall update
         */
        placeOverall: function(playerId) {
            if ($("overallCards-" + playerId)) {
                dojo.destroy("overallCards-" + playerId);
            }

            dojo.place(this.format_block("jstpl_overallCards", {
                playerId: playerId
            }), "overall_player_board_" + playerId);
        },

        updateOverallBettingCards: function(bettingCards) {
            this._overallBettingCardStock.forEach(stock => {
                stock.removeAll();
            });
            bettingCards.forEach(card => {
                const overallBettingCardStock = this._overallBettingCardStock.get(card.weight);
                overallBettingCardStock.addToStockWithId(card.color, card.id);
            });
        },

        updateOverallBettedCards: function(bettedCards) {
            this._overallBettedCardStock.forEach(stock => {
                stock.removeAll();
            });
            bettedCards.forEach(card => {
                const overallBettedCardStock = this._overallBettedCardStock.get(card.weight);
                var uniqueId = this.getBettingCardUniqueId(Number(card.color), Number(card.value));
                overallBettedCardStock.addToStockWithId(uniqueId, card.id);
            });
        },

        updateOverallTrophyCards: function(trophyCards) {
            this._overallTrophyCardStock.forEach(stock => {
                stock.removeAll();
            });
            trophyCards.forEach(card => {
                const overallTrophyCardStock = this._overallTrophyCardStock.get(card.weight);
                overallTrophyCardStock.addToStockWithId(card.value, card.id);
            });
        },

    });
});