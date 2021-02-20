define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.playerTrait", null, {
        constructor() {
            this._notifications.push(
                ['attack', 1600],
                ['defense', 1600],
                ['pass', 1000],
                ['retreat', 1600],
                ['changeRole', 1000],
                ['updatePlayer', 1000]
            );
        },

        notif_attack: function(notif) {
            console.log("attack " + notif.args.cards);
            this.placeAttackCards(notif.args.player_id, notif.args.cards);
        },

        notif_defense: function(notif) {
            this.placeDefenseCards(notif.args.player_id, notif.args.cards);
        },

        notif_pass: function(notif) {

        },

        notif_retreat: function(notif) {

        },

        notif_changeRole: function(notif) {

        },

        notif_updatePlayer: function(notif) {
        },
  
        onPlayerHandSelectionChanged: function () {
            var items = this._playerHand.getSelectedItems();

            // 선택한 카드가 1장 이상일 때
            if (items.length > 0) {
                if (this.checkAction('attack', true)) {
                }
                else {
                    this._playerHand.unselectAll();
                }
            }
        },

        onClickAttackButton: function() {
            if (this.checkAction('attack', true)) {
                let items = this._playerHand.getSelectedItems();
                
                if (items.length > 0) {
                    let card_ids = [];
                    items.forEach(card => {
                        card_ids.push(card.id);
                    });
                    let data = {
                        cards: card_ids.join(';'),
                    }
                    this.takeAction("attack", data);
                    this._playerHand.unselectAll();
                }
            } else {
                this._playerHand.unselectAll();
            }
        },
        
        onClickDefenseButton: function() {
            if (this.checkAction('defense', true)) {
                let items = this._playerHand.getSelectedItems();
                
                if (items.length > 0) {
                    let card_ids = [];
                    items.forEach(card => {
                        card_ids.push(card.id);
                    });
                    let data = {
                        cards: card_ids.join(';'),
                    }
                    this.takeAction("defense", data);
                    this._playerHand.unselectAll();
                }
            } else {
                this._playerHand.unselectAll();
            }
        },

        onClickPassButton: function() {
            if (this.checkAction('pass', true)) {
                this.takeAction('pass');
            }
        },

        updatePlayer: function() {
        },
    });
});