define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/stock",], (dojo, declare) => {
    return declare("customgame.game", ebg.core.gamegui, {
        /*
         * Constructor
         */
        constructor: function() {
            this._notifications = [];
            this._activeStates = [];

            this._cardWidth = 72;
            this._cardHeight = 96;

            this._playerHand = new ebg.stock();
            this._playerHand.create(this, $('hand'), this._cardWidth, this._cardHeight);
            this._playerHand.image_items_per_row = 10;
        },

        setLoader: function(value, max) {
            this.inherited(arguments);
            if (!this.isLoadingComplete && value >= 100) {
              this.isLoadingComplete = true;
              this.onLoadingComplete();
            }
        },

        onLoadingComplete: function() {
            console.log('Loading complete');
        },

        setup: function(gamedatas) {
            // 플레이어 스톡 추가 및 핸드 초기화
            
            for (var color = 0; color < 3; color++)
            {
                for (var value = 1; value <= 10; value++)
                {
                    var card_type_id = this.getCardUniqueId(color, value);
                    this._playerHand.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.jpg', card_type_id);
                }
            }

            for (var i in this.gamedatas.hand) {
                var card = this.gamedatas.hand[i];
                this._playerHand.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id);
            }

            dojo.connect( this._playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged' );

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

            if(reEnterStateOnError){
                promise.catch(() => this.onEnteringState(this.gamedatas.gamestate.name, this.gamedatas.gamestate) );
            }
       
            return promise;
        },

        onEnteringState: function (stateName, args) {
            console.log('Entering state: ' + stateName);

            switch (stateName) {

                /* Example:
                
                case 'myGameState':
                
                    // Show some HTML block at this game state
                    dojo.style( 'my_html_block_id', 'display', 'block' );
                    
                    break;
               */


                case 'dummmy':
                    break;
            }
        },

        onLeavingState: function (stateName) {
            console.log('Leaving state: ' + stateName);

            switch (stateName) {

                /* Example:
                
                case 'myGameState':
                
                    // Hide the HTML block we are displaying only during this game state
                    dojo.style( 'my_html_block_id', 'display', 'none' );
                    
                    break;
               */


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

        onPreferenceChange(pref, newValue){
        },

        getCardUniqueId: function (color, value) {
            // 행 + 열 = 위치값
            return (color * 10) + (value - 1);
        },
    });
});