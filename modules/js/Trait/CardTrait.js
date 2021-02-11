define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.cardTrait", null, {
        constructor() {
            this._notifications.push(
                ['cardPlay', 1000]
            );
        },
    });
});