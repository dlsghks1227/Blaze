/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * BlazeBananani implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * blazebananani.js
 *
 * BlazeBananani user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo", "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",

    g_gamethemeurl + "modules/js/Game/Game.js",

    g_gamethemeurl + "modules/js/States/CardSelectorTrait.js",

    g_gamethemeurl + "modules/js/Trait/CardTrait.js",
    g_gamethemeurl + "modules/js/Trait/PlayerTrait.js",
],
    function (dojo, declare) {
        return declare("bgagame.blazebananani", [
            customgame.game,
            
            blaze.cardSelectorTrait,

            blaze.cardTrait,
            blaze.playerTrait,
        ], {
            constructor: function () {
                console.log('blazebananani constructor');

                // Here, you can init the global variables of your user interface
                // Example:
                // this.myGlobalValue = 0;
                this._cardWidth = 72;
                this._cardHeight = 96;
    
                this._playerHand = new ebg.stock();
                this._playerHand.create(this, $('hand'), this._cardWidth, this._cardHeight);
                this._playerHand.image_items_per_row = 10;
                this._playerHand.centerItems = true;
            },

            /*
                setup:
                
                This method must set up the game user interface according to current game situation specified
                in parameters.
                
                The method is called each time the game interface is displayed to a player, ie:
                _ when the game starts
                _ when a player refreshes the game page (F5)
                
                "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
            */

            setup: function (gamedatas) {
                console.log("Starting game setup");

                // 플레이어 스톡 추가 및 핸드 초기화
                for (var color = 0; color < 3; color++)
                {
                    for (var value = 1; value <= 10; value++)
                    {
                        var card_type_id = this.getCardUniqueId(color, value);
                        this._playerHand.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.jpg', card_type_id);
                    }
                }

                // 플레이어 설정
                this.gamedatas.playersInfo.forEach(player => {
                    let isCurrent = player.id == this.player_id;
                    player.handCount = isCurrent ? player.hand.length : player.hand;
                    dojo.place(this.format_block('jstpl_players', {
                        playerPos: player.no,
                        playerName: player.name,
                        playerColor: player.color,
                        playerCardsCount: player.handCount,
                    }), 'board');
                    if (isCurrent) {
                        player.hand.forEach(card => this._playerHand.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id));
                    }
                });
                dojo.connect(this._playerHand, "onChangeSelection", this, 'onPlayerHandSelectionChanged');

                // 덱 설정
                dojo.place(this.format_block('jstpl_cardOnTable', {
                    posX: 1,
                    posY: 1,
                    x: this._cardWidth,
                    y: this._cardHeight,
                }), 'table-container');
                
                dojo.place(this.format_block('jstpl_table', {
                    deckCount: this.gamedatas.deckCount,
                }), 'cardOnTable-1-1');

                // 플레이어 수 설정
                dojo.attr("board", "data-players", this.gamedatas.playersInfo.length);

                console.log(this.gamedatas);

                // Setup game notifications to handle (see "setupNotifications" method below)
                //this.setupNotifications();
                
                console.log("Ending game setup");
                this.inherited(arguments);
            },


            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //        
            onUpdateActionButtons: function (stateName, args) {
                console.log('onUpdateActionButtons: ' + stateName);

                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {
                        case 'attack':
                            // 공격 버튼 및 패스버튼 추가
                            this.addActionButton('Attack', _('Attack'), () => this.onClickAttackButton(), null, false, 'blue');
                            this.addActionButton('Pass', _('Pass'), () => this.onClickPassButton(), null, false, 'red');
                            break;
                        /*               
                                         Example:
                         
                                         case 'myGameState':
                                            
                                            // Add 3 action buttons in the action status bar:
                                            
                                            this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                                            this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                                            this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                                            break;
                        */
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            /*
            
                Here, you can defines some utility methods that you can use everywhere in your javascript
                script.
            
            */

            ///////////////////////////////////////////////////
            //// Player's action

            /*
            
                Here, you are defining methods to handle player's action (ex: results of mouse click on 
                game objects).
                
                Most of the time, these methods:
                _ check the action is possible at this game state.
                _ make a call to the game server
            
            */

            /* Example:
            
            onMyMethodToCall1: function( evt )
            {
                console.log( 'onMyMethodToCall1' );
                
                // Preventing default browser reaction
                dojo.stopEvent( evt );
    
                // Check that this action is possible (see "possibleactions" in states.inc.php)
                if( ! this.checkAction( 'myAction' ) )
                {   return; }
    
                this.ajaxcall( "/blazebananani/blazebananani/myAction.html", { 
                                                                        lock: true, 
                                                                        myArgument1: arg1, 
                                                                        myArgument2: arg2,
                                                                        ...
                                                                     }, 
                             this, function( result ) {
                                
                                // What to do after the server call if it succeeded
                                // (most of the time: nothing)
                                
                             }, function( is_error) {
    
                                // What to do after the server call in anyway (success or failure)
                                // (most of the time: nothing)
    
                             } );        
            },        
            
            */


            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            /*
                setupNotifications:
                
                In this method, you associate each of your game notifications with your local method to handle it.
                
                Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                      your blazebananani.game.php file.
            
            */
            // setupNotifications: function () {
            //     console.log('notifications subscriptions setup');

            //     // TODO: here, associate your game notifications with local methods

            //     // Example 1: standard notification handling
            //     // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

            //     // Example 2: standard notification handling + tell the user interface to wait
            //     //            during 3 seconds after calling the method in order to let the players
            //     //            see what is happening in the game.
            //     // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            //     // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            //     // 
            // },

            // TODO: from this point and below, you can write your game notifications handling methods

            /*
            Example:
            
            notif_cardPlayed: function( notif )
            {
                console.log( 'notif_cardPlayed' );
                console.log( notif );
                
                // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
                
                // TODO: play the card in the user interface.
            },    
            
            */
        });
    });
