/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * BlazeBananani implementation : © <Inhwan Lee> <dlsghks1227@gmail.com>
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
                this._cardWidth = 108;
                this._cardHeight = 168;

                this._card50Width = 72;
                this._card50Height = 112;

                this._miniCardWidth = 36;
                this._miniCardHeight = 56;

                this._tokenWidth = 108;
                this._tokenHeight = 168;

                this._discardCard = new ebg.stock();
                this._discardCard.create(this, $('discard-cards'), this._cardWidth, this._cardHeight);
                this._discardCard.image_items_per_row = 10;
                this._discardCard.centerItems = true;
                this._discardCard.setOverlap(1, 0);
                this._discardCard.setSelectionMode(0);

                this._trophyCard = new ebg.stock();
                this._trophyCard.create(this, $('trophy-cards'), this._cardWidth, this._cardHeight);
                this._trophyCard.image_items_per_row = 5;
                this._trophyCard.centerItems = true;
                this._trophyCard.setOverlap(1, 0);
                this._trophyCard.setSelectionMode(0);

                this._playerHand = new ebg.stock();
                this._playerHand.create(this, $('hand-cards'), this._cardWidth, this._cardHeight);
                this._playerHand.image_items_per_row = 10;
                this._playerHand.centerItems = true;
                this._playerHand.setOverlap(60, 0);
                this._playerHand.setSelectionAppearance('class');
                
                this._playerToken = new ebg.stock();
                this._playerToken.create(this, $('batting-cards'), this._cardWidth, this._cardHeight);
                this._playerToken.image_items_per_row = 2;
                this._playerToken.centerItems = true;
                this._playerToken.setOverlap(60, 0);
                this._playerToken.setSelectionAppearance('class');

                this._attackCardPlace = new ebg.stock();
                this._attackCardPlace.create(this, $('attackCardOnTable'), this._cardWidth, this._cardHeight);
                this._attackCardPlace.image_items_per_row = 10;
                this._attackCardPlace.centerItems = true;
                this._attackCardPlace.setOverlap(70, 0);
                this._attackCardPlace.setSelectionMode(0);

                this._defenseCardPlace = new ebg.stock();
                this._defenseCardPlace.create(this, $('defenseCardOnTable'), this._cardWidth, this._cardHeight);
                this._defenseCardPlace.image_items_per_row = 10;
                this._defenseCardPlace.centerItems = true;
                this._defenseCardPlace.setOverlap(70, 0);
                this._defenseCardPlace.setSelectionMode(0);

                this._deck = new ebg.stock();
                this._deck.create(this, $('deckOnTable'), this._cardWidth, this._cardHeight);
                this._deck.image_items_per_row = 1;
                this._deck.setOverlap(1, 0);
                this._deck.setSelectionMode(0);

                this._otherPlayerHand = new Map();
                this._otherPlayerToken = new Map();
                this._otherplayerBettedCard = new Map();
                this._otherplayerBettingCard = new Map();
                this._otherplayerTrophyCard = new Map();

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
                        this._playerHand.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.png', card_type_id);
                        this._attackCardPlace.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.png', card_type_id);
                        this._defenseCardPlace.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.png', card_type_id);
                        this._discardCard.addItemType(card_type_id, card_type_id, g_gamethemeurl + 'img/cards.png', card_type_id);
                    }
                }
                this._deck.addItemType(0, 0, g_gamethemeurl + 'img/card_back.png', 0);

                // 트로피 카드 초기화
                for (var value = 0; value < 5; value++) {
                    this._trophyCard.addItemType(value, value, g_gamethemeurl + 'img/trophy_cards.png', value);
                }

                // 플레이어 토큰 설정 및 초기화
                this.gamedatas.battingCards.forEach(card => {
                    if (card.location_arg == this.player_id) {
                        this._playerToken.addItemType(card.id, card.value, g_gamethemeurl + 'img/batting_cards.png', (card.type * 2) + Number(card.value));
                    }
                });
                dojo.connect(this._playerToken, "onChangeSelection", this, 'onPlayerBattingSelectionChanged');

                // 플레이어 설정
                this.gamedatas.playersInfo.forEach(player => {
                    let isCurrent = player.id == this.player_id;
                    player.handCount = isCurrent ? player.hand.length : player.hand;

                    if (isCurrent) {
                        player.hand.forEach(card => this._playerHand.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id));
                        player.battingCards.forEach(card => this._playerToken.addToStock(card.id));
                    }

                    dojo.place(this.format_block('jstpl_players', {
                        playerId: player.id,
                        playerPos: player.no,
                        playerName: player.name,
                        playerColor: player.color,
                    }), 'board');

                    this._otherPlayerHand.set(player.id, new ebg.stock());
                    let playerHand = this._otherPlayerHand.get(player.id);
                    playerHand.create(this, $('player-cards-' + player.id), this._card50Width, this._card50Height);
                    playerHand.image_items_per_row = 1;
                    playerHand.setSelectionMode(0);
                    playerHand.setOverlap(10, 0);
                    playerHand.addItemType(0, 0, g_gamethemeurl + 'img/card_back_mini.png', 0);

                    for (var i = 0; i < player.handCount; i++) {
                        playerHand.addToStock(0);
                    }

                    // 배팅한 토큰 설정
                    dojo.place(this.format_block('jstpl_overallCards', {
                        playerId: player.id,
                    }), 'overall_player_board_' + player.id);

                    this._otherplayerBettingCard.set(player.id, new ebg.stock());
                    let playerBettingCard = this._otherplayerBettingCard.get(player.id);
                    playerBettingCard.create(this, $('player-mini-betting-cards-' + player.id,), this._miniCardWidth, this._miniCardHeight);
                    playerBettingCard.image_items_per_row = 5;
                    playerBettingCard.setSelectionMode(0);
                    playerBettingCard.setOverlap(20, 0);                    
                    this.gamedatas.battingCards.forEach(card => playerBettingCard.addItemType(card.id, card.value, g_gamethemeurl + 'img/batting_cards_back_mini.png', card.type));

                    this._otherplayerBettedCard.set(player.id, new ebg.stock());
                    let playerBettedCard = this._otherplayerBettedCard.get(player.id);
                    playerBettedCard.create(this, $('player-mini-betted-cards-' + player.id,), this._miniCardWidth, this._miniCardHeight);
                    playerBettedCard.image_items_per_row = 5;
                    playerBettedCard.setSelectionMode(0);
                    playerBettedCard.setOverlap(20, 0);                    
                    this.gamedatas.battingCards.forEach(card => playerBettedCard.addItemType(card.id, card.value, g_gamethemeurl + 'img/batting_cards_mini.png', card.type));

                    this._otherplayerTrophyCard.set(player.id, new ebg.stock());
                    let playerTrophyCard = this._otherplayerTrophyCard.get(player.id);
                    playerTrophyCard.create(this, $('player-mini-trophy-cards-' + player.id,), this._miniCardWidth, this._miniCardHeight);
                    playerTrophyCard.image_items_per_row = 5;
                    playerTrophyCard.setSelectionMode(0);
                    playerTrophyCard.setOverlap(20, 0);   

                    for (var value = 0; value < 5; value++) {
                        playerTrophyCard.addItemType(value, value, g_gamethemeurl + 'img/trophy_cards.png', value);
                    }              

                    // this.connect($('blaze-player-' + player.id), "onclick", () => this.onClickBattingButton(player.id));
                    // this.connect($('blaze-player-' + player.id), "onmouseenter", () => this.onMouseEnter(player.id));
                    // this.connect($('blaze-player-' + player.id), "onmouseleave", () => this.onMouseLeave(player.id));
                });
                dojo.connect(this._playerHand, "onChangeSelection", this, 'onPlayerHandSelectionChanged');

                

                // 플레이어 토큰 설정
                this.gamedatas.battingCards.forEach(card => {
                    this._otherPlayerToken.set(card.location_arg, new ebg.stock());
                    let playerToken = this._otherPlayerToken.get(card.location_arg);
                    playerToken.create(this, $('player-token-cards-' + card.location_arg), this._card50Width, this._card50Height);
                    // playerToken.create(this, $('overall_player_board_' + card.location_arg), this._card50Width, this._card50Height);
                    playerToken.image_items_per_row = 5;
                    playerToken.setSelectionMode(0);
                    playerToken.setOverlap(50, 0);
                    playerToken.addItemType(0, 0, g_gamethemeurl + 'img/batting_cards_back_50.png', card.type);
                });

                this.gamedatas.battingCards.forEach(card => {
                    let playerToken = this._otherPlayerToken.get(card.location_arg);
                    playerToken.addToStock(0);
                });

                // 덱 설정
                this._deck.removeAll();
                for (var i = 0; i < (this.gamedatas.deckCards.length / 3); i++) {
                    this._deck.addToStock(0);
                }

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

            onMouseEnter: function(playerId) {
                dojo.query('#blaze-player-' + playerId).style('background-color', '#f7f19e');
            },

            onMouseLeave: function(playerId) {
                dojo.query('#blaze-player-' + playerId).style('background-color', 'rgba(0, 0, 0, 0.65)');
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
        });
    });
