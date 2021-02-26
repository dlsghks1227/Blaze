define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.cardTrait", null, {
        constructor: function() {
            this._notifications.push(
                ["drawCard", 1200],
                ["defenseFailed", 2000],
                ["defenseSuccess", 2000],
                ["getTrophyCard", 1500],      
            );

        },

        notif_drawCard: function(notif) {
            this.drawCards(notif.args.player_id, notif.args.cards, notif.args.deckCount, notif.args.trumpSuitCard);
        },

        notif_defenseFailed: function(notif) {
            this.drawAttackCardsAndDefenseCards(notif.args.player_id, notif.args.attackCards, notif.args.defenseCards);
        },

        notif_defenseSuccess: function(notif) {
            this.discardCards(notif.args.attackCards, notif.args.defenseCards);
        },

        notif_getTrophyCard: function(notif) {
            notif.args.players.forEach(player => {
                this.placeScore(player.id, player.score);
            });
            this.getTrophyCard(notif.args.player_id, notif.args.trophyCard);
        },

        placeCard: function(posX, posY, color, value, sizeX = 1, sizeY = 1, isBack = false, angle = 0, index = 0) {
            if ($('cardOnTable-' + posX + '-' + posY)) {
                dojo.destroy('cardOnTable-' + posX + '-' + posY);
            }
            if (color == -1 && value == -1) {
                return;
            }
            dojo.place(this.format_block('jstpl_cardOnTable', {
                posX: posX,
                posY: posY,
                x: isBack ? 0 : this._cardWidth_L  * (value - 1),
                y: isBack ? 0 : this._cardHeight_L * (color),
                filp: isBack ? 'back' : 'front',
                sizeX: sizeX,
                sizeY: sizeY,
                angle: angle,
                index: index,
            }), 'table-container');
        },

        placeText: function(posX, posY, size, text) {
            if ($('textOnTable-' + posX + '-' + posY)) {
                dojo.destroy('textOnTable-' + posX + '-' + posY);
            }

            dojo.place(this.format_block('jstpl_textOnTable', {
                posX: posX,
                posY: posY,
                size: size,
                text: String(text),
            }), 'table-container');
        },

        placeScore: function(playerId, score) {
            if ($('player-score-' + playerId)) {
                dojo.destroy('player-score-' + playerId);
            }

            dojo.place(this.format_block('jstpl_player_score', {
                playerId: playerId,
                score: score
            }), 'player-container-' + playerId);
        },

        placeAttackCards: function(playerId, cards) {
            cards.forEach(card => {
                this._otherPlayerHand.get(playerId).removeFromStock(0);
                if (playerId != this.player_id) {
                    this._attackCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'blaze-player-' + playerId);
                } else {
                    if ($('hand-cards_item_' + card.id)) {
                        this._attackCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'hand-cards_item_' + card.id);
                        this._playerHand.removeFromStockById(card.id);
                    }
                }
            });
        },

        placeDefenseCards: function(playerId, cards) {
            cards.forEach(card => {
                this._otherPlayerHand.get(playerId).removeFromStock(0);
                if (playerId != this.player_id) {
                    this._defenseCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'blaze-player-' + playerId);
                    this._defenseCardPlace.changeItemsWeight({[this.getCardUniqueId(card.type, card.value)]: card.location_arg});
                } else {
                    if ($('hand-cards_item_' + card.id)) {
                        this._defenseCardPlace.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'hand-cards_item_' + card.id);
                        this._defenseCardPlace.changeItemsWeight({[this.getCardUniqueId(card.type, card.value)]: card.location_arg});

                        this._playerHand.removeFromStockById(card.id);
                    }
                }
            });
        },

        drawCards: function(playerId, cards, deckCount, trumpSuitCard) {
            cards.forEach(card => {
                this._otherPlayerHand.get(playerId).addToStock(0, 'deckOnTable');
                this.placeText(1, 3, 1, deckCount);

                if (playerId == this.player_id) {
                    this._playerHand.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'deckOnTable')
                }
                if (deckCount <= 0) {
                    // 덱에 카드가 없을 때
                    dojo.query('#cardOnTable-1-1 .card').attr('data-filp', 'none');
                }
            });
            this._trumpCardType = trumpSuitCard.type;
            this.placeCard(1, 2, trumpSuitCard.type, trumpSuitCard.value, 1, 1, false, 50, 0);
        },

        discardCards: function(attackCards, defenseCards) {
            attackCards.forEach(card => {
                if ($('attackCardOnTable_item_' + card.id)) {
                    this._discardCard.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'attackCardOnTable_item_' + card.id);
                    this._attackCardPlace.removeFromStockById(card.id);
                }
            });

            defenseCards.forEach(card => {
                if ($('defenseCardOnTable_item_' + card.id)) {
                    this._discardCard.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'defenseCardOnTable_item_' + card.id);
                    this._defenseCardPlace.removeFromStockById(card.id);
                }
            });
        },

        drawAttackCardsAndDefenseCards: function(playerId, attackCards, defenseCards) {
            attackCards.forEach(card => {
                if (playerId != this.player_id) {
                    if ($('attackCardOnTable_item_' + card.id)) {
                        this._otherPlayerHand.get(playerId).addToStock(0, 'attackCardOnTable_item_' + card.id);
                        this._attackCardPlace.removeFromStockById(card.id);
                    }
                } else {
                    if ($('attackCardOnTable_item_' + card.id)) {
                        this._playerHand.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'attackCardOnTable_item_' + card.id);
                        this._otherPlayerHand.get(playerId).addToStock(0);

                        this._attackCardPlace.removeFromStockById(card.id);
                    }
                }
            });

            defenseCards.forEach(card => {
                if (playerId != this.player_id) {
                    if ($('defenseCardOnTable_item_' + card.id)) {
                        this._otherPlayerHand.get(playerId).addToStock(0, 'defenseCardOnTable_item_' + card.id);
                        this._defenseCardPlace.removeFromStockById(card.id);
                    }
                } else {
                    if ($('defenseCardOnTable_item_' + card.id)) {
                        this._playerHand.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id, 'defenseCardOnTable_item_' + card.id);
                        this._otherPlayerHand.get(playerId).addToStock(0);

                        this._defenseCardPlace.removeFromStockById(card.id);
                    }
                }
            });
        },

        betting: function(playerId, playerNo, selectPlayerId, card) {
            if ($('batting-cards_item_' + card.id)) {
                this._otherplayerBettingCard.get(selectPlayerId).addToStock(Number(playerNo) - 1, 'batting-cards_item_' + card.id);
                this._otherPlayerToken.get(playerId).removeFromStockById(Number(playerNo));
                this._playerToken.removeFromStockById(card.id);
            }
        },

        endBetting: function(players, bettingCards, playerTokens) {
            // 여기 문제가 있어서 지우고 다시 로드하는게 좋을 듯
            bettingCards.forEach(card => {
                let playerBettingCard = this._otherplayerBettingCard.get(card.location_arg);
                playerBettingCard.removeAll();
            });

            bettingCards.forEach(card => {
                let playerBettingCard = this._otherplayerBettingCard.get(card.location_arg);
                playerBettingCard.addToStock(card.type);
            });

            playerTokens.forEach(card => {
                let playerToken = this._otherPlayerToken.get(card.location_arg);
                playerToken.removeAll();
            });

            playerTokens.forEach(card => {
                let playerToken = this._otherPlayerToken.get(card.location_arg);
                playerToken.addToStock(0);
            });
        },

        getCardUniqueId: function (color, value) {
            // 행 + 열 = 위치값
            return (color * 10) + (value - 1);
        },

        getCardType: function (type) {
            return {
                color : Math.floor(type / 10),
                value : (type % 10) + 1
            }
        },

        getTrophyCard: function(playerId, trophyCard) {
            if ($('trophy-cards_item_' + trophyCard.id)) {
                this._otherplayerTrophyCard.get(playerId).addToStockWithId(trophyCard.value, trophyCard.id, 'trophy-cards_item_' + trophyCard.id);
                this._trophyCard.removeFromStockById(trophyCard.id);
            }
        },

        setOpacityOnCards: function (stock, value) {
            stock.getAllItems().forEach(card => {
                var div = stock.getItemDivId(card.id);
                dojo.query('#' + div).style('opacity', value);
            });
        }
    });
});