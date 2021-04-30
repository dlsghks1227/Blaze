/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Blaze implementation : © <Inhwan Lee> <dlsghks1227@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Blaze.js
 *
 * Blaze user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock",

    g_gamethemeurl + "modules/js/Game.js",

    g_gamethemeurl + "modules/js/PlayerTrait.js",
    g_gamethemeurl + "modules/js/PlayerEvent.js",

    g_gamethemeurl + "modules/js/CardTrait.js",
    g_gamethemeurl + "modules/js/Cards/TrophyCards.js",
    g_gamethemeurl + "modules/js/Cards/OverallCards.js",
    g_gamethemeurl + "modules/js/Cards/AttackCardsOnTable.js",
    g_gamethemeurl + "modules/js/Cards/DefenseCardsOnTable.js",
],
function (dojo, declare) {
    return declare("bgagame.blaze", [
        customgame.game,

        blaze.playerTrait,
        blaze.playerEvent,
        
        blaze.cardTrait,
        blaze.trophyCards,
        blaze.overallCards,
        blaze.attackCardsOnTable,
        blaze.defenseCardsOnTable,

    ], {
        constructor: function() {
            this._CARD_WIDTH_L  = 108;
            this._CARD_HEIGHT_L = 168;
            
            this._CARD_WIDTH_M  = 72;
            this._CARD_HEIGHT_M = 112;

            this._CARD_WIDTH_S  = 36;
            this._CARD_HEIGHT_S = 56;
        },

        setup: function( gamedatas )
        {
            dojo.destroy("debug_output");

            const playerPlace = this.getPlayerPlaceReorder(this.player_id, this.gamedatas.nextPlayerTable);
            
            // --------- 플레이어 설정 ---------
            this.createPlayerCardStock();
            this.createPlayerBettingCardStock();
            this.setupPlayersPlace(this.gamedatas.blazePlayers, this.player_id, playerPlace);

            // --------- overall setting ---------
            this.updateOverallCards(
                this.gamedatas.overallBettingCards,
                this.gamedatas.overallBettedCards,
                this.gamedatas.overallTrophyCards,
            );

            // --------- 덱 카드 수 설정 ---------
            this.updateDeckCount(this.gamedatas.deckCount);

            // --------- 트럼프 카드 설정 ---------
            var trumpCard = this.gamedatas.trumpCard;
            this.updateTrumpCard(trumpCard.color, trumpCard.value);

            // --------- 트로피 카드 설정 ---------
            this.setupTrophyCards(this.gamedatas.trophyCards);

            // --------- 버려진 카드 설정 ---------
            this.updateDiscardCard(this.gamedatas.dicardCard);

            // --------- 테이블 위 공격 카드 설정 ---------
            this.setupAttackCards(this.gamedatas.attackCardsOnTable);
            
            // --------- 테이블 위 방어 카드 설정 ---------
            this.setupDefenseCards(this.gamedatas.defenseCardsOnTable);

            // --------- 플레이어 수 설정 ---------
            dojo.attr("board", "data-players", this.gamedatas.blazePlayers.length);

            this.inherited(arguments);
        }, 

        onUpdateActionButtons: function( stateName, args )
        {
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
                case 'playerTurn':
                    var activePlayerRole = args.activePlayerRole;
                    var attackCardsOnTable = args.attackCardOnTable;
                    var limitCardCount = (args.limitCardCount > 5 ? 5 : args.limitCardCount);
                    const allItems = this._playerCardStock.getAllItems();

                    if (activePlayerRole == "1") {          // attacker
                        // if (limitCardCount == attackCardsOnTable.length) {
                        //     this.takeAction('pass');
                        // }
                        if (allItems.length <= 0) {
                            this.takeAction('pass');
                        }
                        var isPassEnabled = (attackCardsOnTable.length > 0);
                        this.setupAttackerButton(isPassEnabled);

                    } else if (activePlayerRole == "2") {   // defender
                        this.setupDefenderButton();
                    } else if (activePlayerRole == "3") {   // supporter
                        // if (limitCardCount == attackCardsOnTable.length) {
                        //     this.takeAction('pass');
                        // }
                        if (allItems.length <= 0) {
                            this.takeAction('pass');
                        }
                        this.setupSupporterButton();
                    }
                    break;
                }
            }
        },

        onMouseEnter: function(playerId) {
            this.updateOtherPlayerRole(playerId, "4");
        },

        onMouseLeave: function(playerId) {
            this.updateOtherPlayerRole(playerId, "0");
        },

        setupAttackerButton: function(isPassEnabled) {
            this.addActionButton("attackButton", _("Attack"), () => this.onClickAttackButton(), null, false, "blue");
            if (isPassEnabled == true) {
                this.addActionButton("passButton", _("Pass"), () => this.onClickPassButton(), null, false, "red");
            }
        },

        setupDefenderButton: function() {
            this.addActionButton("defenseButton", _("Defense"), () => this.onClickDefenseButton(), null, false, "blue");
            this.addActionButton("passButton", _("Pass"), () => this.onClickPassButton(), null, false, "red");
        },

        setupSupporterButton: function() {
            this.addActionButton("attackButton", _("Attack"), () => this.onClickAttackButton(), null, false, "blue");
            this.addActionButton("passButton", _("Pass"), () => this.onClickPassButton(), null, false, "red");
        },
    });
});
