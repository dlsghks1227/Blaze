define(["dojo", "dojo/_base/declare", "ebg/stock"], (dojo, declare) => {
    return declare("blaze.cardSelectorTrait", null, {
        constructor: function () {

        },

        onPlayerHandSelectionChanged: function () {
            var items = this._playerHand.getSelectedItems();

            // 선택한 카드가 1장 이상일 때
            if (items.length > 0) {
                // if (this.checkAction('attackCards', true)) {
                //     let card_ids = []
                //     items.forEach(card => {
                //         card_ids.push(card.id);
                //     });
                //     let data = {
                //         cards: card_ids.join(';'),
                //     }
                //     this.takeAction("attackCards", data);
                // }
            }
        },

        onAttackButton: function() {

        }
    })
});