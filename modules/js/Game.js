define(["dojo", "dojo/_base/declare", "ebg/core/gamegui"], (dojo, declare) => {
    return declare("customgame.game", ebg.core.gamegui, {
        /*
         * Constructor
         */
        constructor()
        {
            this._notifications = [];
            this._activeStates = [];

            this._activePlayerRole = 0;
            this._attackCardsOnTable = [];
            this._defenseCardsOnTable = [];
            this._attackedCards = [];
            this._trumpCard = [];

            this._limitCardCount = 0;

            this._invaildPlayerCards = [];
            this._combinedDefenseCards = [];
        },
        
        setLoader: function(value, max) 
        {
            this.inherited(arguments);
            if (!this.isLoadingComplete && value >= 100) {
              this.isLoadingComplete = true;
              this.onLoadingComplete();
            }
        },

        onLoadingComplete: function () 
        {
        },

        setup: function(gamedatas) 
        {
            this.setupNotifications();
        },

        takeAction: function(action, data, reEnterStateOnError) 
        {
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

        onEnteringState: function (stateName, args) 
        {
            switch (stateName) {
                case 'playerTurn':
                    this._activePlayerRole = args.args.activePlayerRole;
                    this._attackCardsOnTable = args.args.attackCardOnTable;
                    this._defenseCardsOnTable = args.args.defenseCards;
                    this._attackedCards = args.args.attackedCards;
                    this._trumpCard = args.args.trumpCard;
                    this._limitCardCount = args.args.limitCardCount;

                    if( this.isCurrentPlayerActive() ) {
                        if (this._activePlayerRole == "1" || this._activePlayerRole == "3") {
                            this._playerCardStock.setSelectionMode(2);
                            if (this._attackCardsOnTable.length > 0 || this._defenseCardsOnTable.length > 0) {
                                this._invaildPlayerCards = this.updateInvaildPlayerCard();
                            }
                        } else if (this._activePlayerRole == "2") {
                            this._playerCardStock.setSelectionMode(1);
                            this._attackCardStock.setSelectionMode(1);
                        }
                    } else {
                        this._playerCardStock.setSelectionMode(0);
                        this._attackCardStock.setSelectionMode(0);
                    }

                    this._invaildPlayerCards = [];
                    this._combinedDefenseCards = [];

                    break;
                case 'startOfBetting':
                    this._playerBettingCardStock.setSelectionMode(1);

                    this.gamedatas.blazePlayers.forEach(player => {
                        this.updateOtherPlayerRole(player.id, "0");


                        if (player.id != this.player_id) {
                            this.connect($('otherPlayer-' + player.id), "onclick",         () => this.onClickBettingButton(player.id));
                            this.connect($('otherPlayer-' + player.id), "onmouseenter",    () => this.onMouseEnter(player.id));
                            this.connect($('otherPlayer-' + player.id), "onmouseleave",    () => this.onMouseLeave(player.id));
                        }
                    });
                    break;
                case 'dummmy':
                    break;
            }
        },

        onLeavingState: function (stateName) 
        {
            switch (stateName) {
                case 'playerTurn':
                    this._firstAttackCardValue = -1;

                    this._invaildAttackCardOnTable = [];
                    this._selectedDefenseCardId = -1;

                    this._invaildPlayerCards = [];
                    this._combinedDefenseCards = [];

                    this._attackCardStock.setSelectionMode(0);

                    this.resetSelectedCardsInStock(this._playerCardStock);
                    this.resetSelectedCardsInStock(this._attackCardStock);

                    break;
                case 'startOfBetting':
                    this._playerBettingCardStock.setSelectionMode(0);

                    this.gamedatas.blazePlayers.forEach(player => {
                        this.updateOtherPlayerRole(player.id, "0");

                        if (player.id != this.player_id) {
                            this.disconnect($('otherPlayer-' + player.id), "onclick",         () => this.onClickBettingButton(player.id));
                            this.disconnect($('otherPlayer-' + player.id), "onmouseenter",    () => this.onMouseEnter(player.id));
                            this.disconnect($('otherPlayer-' + player.id), "onmouseleave",    () => this.onMouseLeave(player.id));
                        }
                    });
                    break;
                case 'dummmy':
                    break;
            }
        },

        setupNotifications: function() 
        {
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
        }
    });
});
