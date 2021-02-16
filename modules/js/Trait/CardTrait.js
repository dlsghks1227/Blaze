define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.cardTrait", null, {
        constructor: function() {
            this._notifications.push(
                ["placeCard", 1000],
                ["drawCard", 1200]
            );
        },

        placeCard: function(posX, posY, color, value) {
            if($("cardOnTable-" + posX + "-" + posY)) {
                dojo.destroy("cardOnTable-" + posX + "-" + posY);
                }
        
            dojo.place(this.format_block('jstpl_cardOnTable', {
                posX: posX,
                posY: posY,
                x: this._cardWidth * (value - 1),
                y: this._cardHeight * color,
            }), 'table-container');
        },

        getCardUniqueId: function (color, value) {
            // 행 + 열 = 위치값
            return (color * 10) + (value - 1);
        },

        notif_placeCard: function(notif) {
            console.log(notif);
        },

        notif_drawCard(notif) {
            console.log("asdfasdfasdfdsf" + notif)
            for (var i in notif.args.cards) {
                var card = notif.args.cards[i];
                var color = card.type;
                var value = card.type_arg;
                this._playerHand.addToStockWithId(this.getCardUniqueId(color, value), card.id);
            }
        }
    });
});