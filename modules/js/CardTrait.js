define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.cardTrait", null, {
        constructor: function() {
            this._notifications.push(
                // ["", 1200],
            );
        },
        /*
         * Notifications
         */
        notif_draw: function(notif) {

        },

        /*
         *  Card update
         */
        updateDeckCount: function(count) {
            if ($("deck")) {
                dojo.destroy("deck");
            }

            dojo.place(this.format_block("jstpl_deck", {
                count: count,
            }), "playCardOnTable");
        },

        updateTrumpCard: function(color, value) {
            if ($("trump")) {
                dojo.destroy("trump");
            }

            dojo.place(this.format_block("jstpl_trump", {
                x: this._CARD_WIDTH_L   * (value - 1),
                y: this._CARD_HEIGHT_L  * color,
            }), "playCardOnTable");
        },

        updateDiscardCard: function(color, value) {
            if ($("discard")) {
                dojo.destroy("discard");
            }

            dojo.place(this.format_block("jstpl_discard", {
                x: this._CARD_WIDTH_L   * (value - 1),
                y: this._CARD_HEIGHT_L  * color,
            }), "discardCardOnTable");
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
            return (color * 10) + (value - 1);
        },

        getCardDataWithType: function(cardType) {
            return {
                color: Math.floor(cardType / 10),
                value: (cardType % 10) + 1
            }
        },

        getBettingCardUniqueId: function(color, value) {
          return (color * 2) + value; 
        },
    });
});