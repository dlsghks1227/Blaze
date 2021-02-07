define(["dojo", "dojo/_base/declare", "ebg/core/gamegui"], (dojo, declare) => {
    return declare("customgame.game", ebg.core.gamegui, {
        /*
         * Constructor
         */
        constructor()
        {
            this._notifications = [];
            this._activeStates = [];
        }
    });
});