define(["dojo", "dojo/_base/declare", "ebg/core/gamegui"], (dojo, declare) => {
    return declare("customgame.game", ebg.core.gamegui, {
        /*
         * Constructor
         */
        constructor()
        {
            this._notifications = [];
            this._activeStates = [];

            this._attackedCard = [];
        },

        setLoader: function(value, max) {
            this.inherited(arguments);
            if (!this.isLoadingComplete && value >= 100) {
              this.isLoadingComplete = true;
              this.onLoadingComplete();
            }
        },

        onLoadingComplete: function () {
        },

        setup: function(gamedatas) {
            this.setupNotifications();
        },

        takeAction: function(action, data, reEnterStateOnError) {
            data = data || {};
            data.lock = true;
            let promise = new Promise((resolve, reject) => {
                this.ajaxcall(
                    '/' + this.game_name + '/' + this.game_name + '/' + action + '.html',
                    data,
                    this,
                    (data) => resolve(data),
                    (isError, message, code) => {
                        if (isError) reject(message, code);
                });
            });

            if(reEnterStateOnError) {
                promise.catch(() => this.onEnteringState(this.gamedatas.gamestate.name, this.gamedatas.gamestate) );
            }
       
            return promise;
        },

        onEnteringState: function (stateName, args) {
            switch (stateName) {
                case 'playerTurn':
                    this._activePlayerRole = args.args.activePlayerRole;
                    this._defenderCardsCount = args.args.DefenderCardsCount;
                    this._attackedCard = args.args.attackedCard;
                    this._attackCardsCount = args.args.attackCard.length;
                    this._defenseCards = new Map();
                    break;
                case 'batting':
                    this.gamedatas.playersInfo.forEach(player => {
                        // 플레이어 색상 초기화
                        dojo.query('#blaze-player-' + player.id).attr('data-role', 'none');

                        if (player.id != this.player_id) {
                            this.connect($('blaze-player-' + player.id), "onclick",         () => this.onClickBattingButton(player.id));
                            this.connect($('blaze-player-' + player.id), "onmouseenter",    () => this.onMouseEnter(player.id));
                            this.connect($('blaze-player-' + player.id), "onmouseleave",    () => this.onMouseLeave(player.id));
                        }
                    });
                    break;
                case 'dummmy':
                    break;
            }
        },

        onLeavingState: function (stateName) {
            switch (stateName) {
                case 'playerTurn':
                    // 색 초기화
                    this.setOpacityOnCards(this._playerHand, '1');
                    this._playerHand.unselectAll();
                    break;
                case 'batting':
                    this.updatePlayer(this.gamedatas.playersInfo);
                    this._playerToken.unselectAll();
                    break;
                case 'dummmy':
                    break;
            }
        },

        setupNotifications: function() {
            this._notifications.forEach(notif => {
                var functionName = "notif_" + notif[0];

                dojo.subscribe(notif[0], this, functionName);
                if (notif[1] != null) {
                    this.notifqueue.setSynchronous(notif[0], notif[1]);

                    // notif[0] + Instant는 딜레이 없이 동일한 기능 제공
                    dojo.subscribe(notif[0] + 'Instant', this, functionName);
                    this.notifqueue.setSynchronous(notif[0] + 'Instant', 10);
                }
            });
        },
    });
});