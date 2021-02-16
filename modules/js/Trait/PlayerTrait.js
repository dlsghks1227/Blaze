define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.playerTrait", null, {
        constructor() {
            this._notifications.push(
                ['attackCards', 1600],
            );
        },

        notif_attackCards: function(notif) {
            console.log(notif.args);
        },

        onClickAttackButton: function() {
            console.log("Attack");
        },
        onClickPassButton: function() {
            console.log("Attack");
        },
    });
});