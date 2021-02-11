define(["dojo", "dojo/_base/declare", "ebg/core/gamegui"], (dojo, declare) => {
    return declare("customgame.game", ebg.core.gamegui, {
        /*
         * Constructor
         */
        constructor()
        {
            this._notifications = [];
            this._activeStates = [];
        },

        setLoader(value, max) {
            this.inherited(arguments);
            if (!this.isLoadingComplete && value >= 100) {
              this.isLoadingComplete = true;
              this.onLoadingComplete();
            }
        },

        onLoadingComplete() {
            console.log('Loading complete');
        },

        setup(gamedatas) {
            this.setupNotifications();
        },

        setupNotifications() {
            console.log(this._notifications);
        },
    });
});