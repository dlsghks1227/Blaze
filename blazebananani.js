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
                this._cardWidth_L   = 108;
                this._cardHeight_L  = 168;

                this._cardWidth_M   = 72;
                this._cardHeight_M  = 112;

                this._cardWidth_S   = 36;
                this._cardHeight_S  = 56;

                this._trophyCard        = this.initStock($('trophy-cards'), 'L', 5, 60, true);
                this._playerHand        = this.initStock($('hand-cards'), 'L', 10, 60, true, true);

                this._playerToken       = this.initStock($('batting-cards'), 'L', 2, 60, true, true);
                this._playerToken.setSelectionMode(1);

                this._discardCard       = this.initStock($('discard-cards'), 'L', 10, 1, false);
                this._attackCardPlace   = this.initStock($('attackCardOnTable'), 'L', 10, 70, true);
                this._defenseCardPlace  = this.initStock($('defenseCardOnTable'), 'L', 10, 70, true);

                this._otherPlayerHand           = new Map();
                this._otherPlayerToken          = new Map();
                this._otherplayerBettedCard     = new Map();
                this._otherplayerBettingCard    = new Map();
                this._otherplayerTrophyCard     = new Map();

                this._defenderCardsCount    = 0;
                this._activePlayerRole      = 0;
                this._trumpCardType         = 0;
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

                // 트로피 카드 초기화
                for (var value = 1; value <= 5; value++) {
                    this._trophyCard.addItemType(value, value, g_gamethemeurl + 'img/trophy_cards.png', value - 1);
                }

                // 플레이어 토큰 설정 및 초기화 - 플레이어 수에 맞게 추가
                var bettingCardsNumber = 0;
                this.gamedatas.playersInfo.forEach(player => {
                    this._playerToken.addItemType(bettingCardsNumber,       0, g_gamethemeurl + 'img/batting_cards.png', bettingCardsNumber);
                    this._playerToken.addItemType(bettingCardsNumber + 1,   1, g_gamethemeurl + 'img/batting_cards.png', bettingCardsNumber + 1);
                    this._playerToken.addItemType(bettingCardsNumber + 2,   1, g_gamethemeurl + 'img/batting_cards.png', bettingCardsNumber + 1);
                    bettingCardsNumber += 2;
                });

                dojo.connect(this._playerToken, "onChangeSelection", this, 'onPlayerBattingSelectionChanged');

                // 플레이어 설정
                this.gamedatas.playersInfo.forEach(player => {
                    let isCurrent = player.id == this.player_id;
                    player.handCount = isCurrent ? player.hand.length : player.hand;

                    if (isCurrent) {
                        player.hand.forEach(card => this._playerHand.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id));
                        player.tokenCards.forEach(card => this._playerToken.addToStockWithId((card.type * 2) + Number(card.value), card.id));
                    }

                    dojo.place(this.format_block('jstpl_players', {
                        playerId: player.id,
                        playerPos: player.no,
                        playerName: player.name,
                        playerColor: player.color,
                    }), 'board');

                    this._otherPlayerHand.set(
                        player.id, 
                        this.initStock($('player-cards-' + player.id), 'M', 1, 10, false)
                    );
                    let playerHand = this._otherPlayerHand.get(player.id);
                    playerHand.addItemType(0, 0, g_gamethemeurl + 'img/card_back_mini.png', 0);

                    for (var i = 0; i < player.handCount; i++) {
                        playerHand.addToStock(0);
                    }

                    // 배팅한 토큰 설정
                    dojo.place(this.format_block('jstpl_overallCards', {
                        playerId: player.id,
                    }), 'overall_player_board_' + player.id);

                    this._otherplayerBettingCard.set(
                        player.id, 
                        this.initStock( $('player-mini-betting-cards-' + player.id), 'S', 5, 20, false)
                    );

                    let playerBettingCard = this._otherplayerBettingCard.get(player.id);            
                    this.gamedatas.playersInfo.forEach(player => {
                        playerBettingCard.addItemType(Number(player.no) - 1, 0, g_gamethemeurl + 'img/batting_cards_back_mini.png', Number(player.no) - 1);
                    });

                    this._otherplayerBettedCard.set(
                        player.id,
                        this.initStock( $('player-mini-betted-cards-' + player.id), 'S', 2, 50, false)
                    );
                    let playerBettedCard = this._otherplayerBettedCard.get(player.id);
                    var bettedCardsNumber = 0;
                    this.gamedatas.playersInfo.forEach(player => {
                        playerBettedCard.addItemType(bettedCardsNumber,       0, g_gamethemeurl + 'img/batting_cards_mini.png', bettedCardsNumber);
                        playerBettedCard.addItemType(bettedCardsNumber + 1,   1, g_gamethemeurl + 'img/batting_cards_mini.png', bettedCardsNumber + 1);
                        playerBettedCard.addItemType(bettedCardsNumber + 2,   1, g_gamethemeurl + 'img/batting_cards_mini.png', bettedCardsNumber + 1);
                        bettedCardsNumber += 2;
                    });

                    this._otherplayerTrophyCard.set(
                        player.id,
                        this.initStock( $('player-mini-trophy-cards-' + player.id), 'S', 5, 20, false)
                    );

                    let playerTrophyCard = this._otherplayerTrophyCard.get(player.id);
                    for (var value = 1; value <= 5; value++) {
                        playerTrophyCard.addItemType(value, value, g_gamethemeurl + 'img/trophy_cards_mini.png', value - 1);
                    }              
                });
                dojo.connect(this._playerHand, "onChangeSelection", this, 'onPlayerHandSelectionChanged');

                this.updatePlayer(this.gamedatas.playersInfo);

                // 플레이어 토큰 설정
                this.gamedatas.tokenCards.forEach(card => {
                    this._otherPlayerToken.set(
                        card.location_arg,
                        this.initStock( $('player-token-cards-' + card.location_arg), 'M', 5, 50, false)
                    );
                    let playerToken = this._otherPlayerToken.get(card.location_arg);
                    playerToken.addItemType(0, 0, g_gamethemeurl + 'img/batting_cards_back_50.png', card.type);
                });

                this.gamedatas.tokenCards.forEach(card => {
                    let playerToken = this._otherPlayerToken.get(card.location_arg);
                    playerToken.addToStock(0);
                });
                
                this.gamedatas.bettingCards.forEach(card => {
                    let playerBettingCard = this._otherplayerBettingCard.get(card.location_arg);
                    playerBettingCard.addToStock(card.type);
                });

                this.gamedatas.bettedCards.forEach(card => {
                    let playerBettedCard = this._otherplayerBettedCard.get(card.location_arg);
                    playerBettedCard.addToStock(card.id);
                });

                // 트로피 카드 설정
                this.gamedatas.trophyCards.forEach(card => {
                    if (card.location_arg == this.gamedatas.currentRound) {
                        this._trophyCard.addToStockWithId(card.value, card.value);
                    }
                });

                // 다른 플레이어 트로피 카드 설정
                this.gamedatas.trophyCardsOnPlayer.forEach(card => {
                    let playerTrophyCard = this._otherplayerTrophyCard.get(card.location_arg);
                    playerTrophyCard.addToStockWithId(card.value, card.value);
                });

                // 트럼프 슈트 카드 설정
                this._trumpCardType = this.gamedatas.trumpSuitCard.type;
                this.placeCard(1, 3, this.gamedatas.trumpSuitCard.type, this.gamedatas.trumpSuitCard.value);
                this.placeText(1, 4, 1, "Trump suit");

                // 덱 카드 설정
                if (this.gamedatas.deckCards.length > 0) {
                    this.placeCard(1, 1, 0, 0, true);
                }
                this.placeText(1, 2, 1, this.gamedatas.deckCards.length);
                
                // 공격 및 방어 카드 설정
                this._attackCardPlace.removeAll();
                this.gamedatas.attackCards.forEach(card => {
                    this._attackCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id);
                });

                this._defenseCardPlace.removeAll();
                this.gamedatas.defenseCards.forEach(card => {
                    this._defenseCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id);
                });

                // 버려진 카드 설정
                this._discardCard.removeAll();
                this.gamedatas.discardCards.forEach(card => {
                    this._discardCard.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id);
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
                                // 낼 수 있는 카드 업데이트
                                this.updateEnableAttackCards();
                                this.addActionButton('Attack', _('Attack'), () => this.onClickAttackButton(), null, false, 'blue');
                                if (args.tableOnAttackCards.length > 0) {
                                    this.addActionButton('Pass', _('Pass'), () => this.onClickPassButton(), null, false, 'red');
                                }
                            } else if (args.activePlayerRole == '2') {
                                // 방어할 수 있는 카드 업데이트
                                this.updateEnableDefenseCards();
                                this.addActionButton('Defense', _('Defense'), () => this.onClickDefenseButton(), null, false, 'blue');
                                this.addActionButton('Retreat', _('Retreat'), () => this.onClickPassButton(), null, false, 'red');
                            } else if (args.activePlayerRole == '3') {
                                // 낼 수 있는 카드 업데이트
                                this.updateEnableAttackCards();
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
                dojo.query('#blaze-player-' + playerId).attr('data-role', 'selected');
            },

            onMouseLeave: function(playerId) {
                dojo.query('#blaze-player-' + playerId).attr('data-role', 'none');
            },

            initStock: function(container_div, type, row, overlap, isCenter, isSelected = false) {
                var width   = (type == 'L' ? this._cardWidth_L  : (type == 'M' ? this._cardWidth_M  : this._cardWidth_S));
                var height  = (type == 'L' ? this._cardHeight_L : (type == 'M' ? this._cardHeight_M : this._cardHeight_S));
                var stock   = new ebg.stock();
                stock.create(this, container_div, width, height);
                stock.image_items_per_row = row;
                stock.centerItems = isCenter;
                stock.setOverlap(overlap, 0);
                if (isSelected == false) {
                    stock.setSelectionMode(0);
                } else {
                    stock.setSelectionAppearance('class');
                }

                return stock;
            }

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
