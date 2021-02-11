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
    "ebg/stock",

    g_gamethemeurl + "modules/js/Game/Game.js",

    g_gamethemeurl + "modules/js/Trait/CardTrait.js",
],
    function (dojo, declare) {
        return declare("bgagame.blazebananani", [
            customgame.game,
            blaze.cardTrait,
        ], {
            constructor: function () {
                console.log('blazebananani constructor');

                // Here, you can init the global variables of your user interface
                // Example:
                // this.myGlobalValue = 0;

                // 카드 크기 지정
                this.cardWidth = 72;
                this.cardHeight = 96;
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

                // Setting up player boards
                for (var player_id in gamedatas.players) {
                    var player = gamedatas.players[player_id];

                    // TODO: Setting up players boards if needed
                }

                // TODO: Set up your game interface here, according to "gamedatas"
                this.playerHand = new ebg.stock();
                this.playerHand.create(this, $('hand'), this.cardWidth, this.cardHeight);
                this.playerHand.image_items_per_row = 10;

                for (var color = 0; color < 3; color++)
                {
                    for (var value = 1; value <= 10; value++)
                    {
                        var card_type_id = this.getCardUniqueId(color, value);
                        this.playerHand.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.jpg', card_type_id);
                    }
                }

                // Adding deck / discard
                for (var x = 1; x <= 6; x++) {
                    for (var y = 1; y <= 2; y++) {
                        dojo.place(this.format_block('jstpl_cardOnTable', {
                            posX: x,
                            posY: y,
                            x: 0,
                            y: 0,
                        }), 'table-container');
                    }
                }
                // dojo.connect($('testButton'), "onclick", () => this.onClickButton());

                var count = 1;
                for (var i in this.gamedatas.players) {
                    var player = gamedatas.players[i];
                    dojo.place(this.format_block('jstpl_players', {
                        playerPos: count,
                        playerName: player.name,
                        playerColor: player.color,
                        playerCardsCount: this.gamedatas.countCards[player.id],
                    }), 'board');
                    count += 1;
                }

                dojo.attr("board", "data-players", gamedatas.playersNumber);
                // dojo.place(this.format_block('jstpl_player', {
                // }))

                // board 안의 data-players를 이용하여 플레이어 수에 따른 배치

                // // stock 객체를 사용
                // this.playerHand = new ebg.stock();
                // // .tpl 파일의 div id 컨테이너를 지정 (id = "myHand")
                // this.playerHand.create(this, $('myHand'), this.cardWidth, this.cardHeight);

                // // 행당 이미지 지정
                // // 스프라이트 이미지의 한 행당 카드의 개수
                // this.playerHand.image_items_per_row = 13;

                // for (var color = 1; color <= 4; color++)
                // {
                //     for (var value = 2; value <= 14; value++)
                //     {
                //         var cardTypeId = this.getCardUniqueId(color, value);
                //         this.playerHand.addItemType(
                //             cardTypeId,                 // ID
                //             cardTypeId,                 // Weight
                //             g_gamethemeurl + 'img/cards.jpg',// Image
                //             cardTypeId                  // 
                //         )
                //     }
                // }

                // this.playerHand.addToStockWithId(this.getCardUniqueId(2, 5), 42);


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

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
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

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //        
            onUpdateActionButtons: function (stateName, args) {
                console.log('onUpdateActionButtons: ' + stateName);

                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {
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

            getCardUniqueId: function (color, value) {
                // 행 + 열 = 위치값
                return (color * 10) + (value - 1);
            },

            onClickButton() {
                console.log("Test Button");
            },


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
