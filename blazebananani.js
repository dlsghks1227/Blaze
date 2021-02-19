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

                this._tokenWidth = 72;
                this._tokenHeight = 96;
    
                this._playerHand = new ebg.stock();
                this._playerHand.create(this, $('hand'), this._cardWidth, this._cardHeight);
                this._playerHand.image_items_per_row = 10;
                this._playerHand.centerItems = true;

                this._attackCardPlace = new ebg.stock();
                this._attackCardPlace.create(this, $('attackCardOnTable'), this._cardWidth, this._cardHeight);
                this._attackCardPlace.image_items_per_row = 10;
                this._attackCardPlace.centerItems = true;
                this._attackCardPlace.setSelectionMode(0);

                this._defenseCardPlace = new ebg.stock();
                this._defenseCardPlace.create(this, $('defenseCardOnTable'), this._cardWidth, this._cardHeight);
                this._defenseCardPlace.image_items_per_row = 10;
                this._defenseCardPlace.centerItems = true;
                this._defenseCardPlace.setSelectionMode(0);
                
                // ---- 토큰 Stock 추가 예정 -----
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

                // 플레이어 핸드 및 공격, 방어 스톡 이미지 설정 및 초기화
                for (var color = 0; color < 3; color++)
                {
                    for (var value = 1; value <= 10; value++)
                    {
                        var card_type_id = this.getCardUniqueId(color, value);
                        this._playerHand.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.jpg', card_type_id);
                        this._attackCardPlace.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.jpg', card_type_id);
                        this._defenseCardPlace.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.jpg', card_type_id);
                    }
                }

                // 플레이어 토큰 설정 및 초기화
                console.log(this.gamedatas);


                // 플레이어 설정
                this.gamedatas.playersInfo.forEach(player => {
                    let isCurrent = player.id == this.player_id;
                    player.handCount = isCurrent ? player.hand.length : player.hand;
                    dojo.place(this.format_block('jstpl_players', {
                        playerId: player.id,
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
                this.placeCard(1, 1, 0, 1);
                dojo.place(this.format_block('jstpl_textOnTable', {
                    posX: 1,
                    posY: 2,
                    size: 1,
                    text: this.gamedatas.deckCount,
                }), 'table-container');
                console.log(this.gamedatas.deckCount);

                // 트럼프 슈트 카드 설정
                this.placeCard(1, 3, this.gamedatas.trumpSuitCard.type, this.gamedatas.trumpSuitCard.value);
                dojo.place(this.format_block('jstpl_textOnTable', {
                    posX: 1,
                    posY: 4,
                    size: 1,
                    text: "Trump suit",
                }), 'table-container');
                
                // 공격 및 방어 카드 설정
                this._attackCardPlace.removeAll();
                this.gamedatas.attackCards.forEach(card => {
                    this._attackCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id);
                });

                this._defenseCardPlace.removeAll();
                this.gamedatas.defenseCards.forEach(card => {
                    this._defenseCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id);
                });

                // 플레이어 수 설정
                dojo.attr("board", "data-players", this.gamedatas.playersInfo.length);

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
                        case 'playerTurn':
                            if (args.activePlayerRole == '1') {
                                this.addActionButton('Attack', _('Attack'), () => this.onClickAttackButton(), null, false, 'blue');
                                if (args.tableOnAttackCards != 0) {
                                    this.addActionButton('Pass', _('Pass'), () => this.onClickPassButton(), null, false, 'red');
                                }
                            } else if (args.activePlayerRole == '2') {
                                this.addActionButton('Defense', _('Defense'), () => this.onClickDefenseButton(), null, false, 'blue');
                                this.addActionButton('Retreat', _('Retreat'), () => this.onClickPassButton(), null, false, 'red');
                            } else if (args.activePlayerRole == '3') {
                                this.addActionButton('Support', _('Support'), () => this.onClickAttackButton(), null, false, 'blue');
                                this.addActionButton('Pass', _('Pass'),     () => this.onClickPassButton(), null, false, 'red');
                            }
                            break;
                        case 'dummmy':
                            break;
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
        });
    });
