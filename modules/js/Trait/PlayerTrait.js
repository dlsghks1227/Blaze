define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.playerTrait", null, {
        constructor() {
            this._notifications.push(
                ['attack', 1600],
                ['defense', 1600],
                ['pass', 1000],
                ['retreat', 1600],
                ['changeRole', 1000],
                ['updatePlayer', 1000],
                ['betting', 1000],
                ['endBetting', 1500],
                ['roundStart', 2000],
            );

            this._selectCardValue = 0;
            this._trumpCardType = 0;
        },

        notif_attack: function(notif) {
            this.placeAttackCards(notif.args.player_id, notif.args.cards);
        },

        notif_defense: function(notif) {
            this.placeDefenseCards(notif.args.player_id, notif.args.cards);
        },

        notif_pass: function(notif) {
        },

        notif_retreat: function(notif) {

        },

        notif_changeRole: function(notif) {

        },

        notif_betting: function(notif) {
            this.betting(notif.args.player_id, notif.args.player_no, notif.args.select_player_id, notif.args.select_card);
        },

        notif_endBetting: function(notif) {
            this.endBetting(notif.args.players, notif.args.betting_cards, notif.args.player_tokens);
        },

        notif_updatePlayer: function(notif) {
            this.updatePlayer(notif.args.players);
        },

        notif_roundStart: function(notif) {
            this._trumpCardType = notif.args.trumpSuitCard.type;
            this.roundStart(
                notif.args.players,
                notif.args[this.player_id + '_hand'],
                notif.args[this.player_id + '_token'],
                notif.args.tokenCards,
                notif.args.bettingCards,
                notif.args.bettedCards,
                notif.args.trumpSuitCard,
                notif.args.deckCards,
                notif.args.trophyCards,
                notif.args.trophyCardsOnPlayer,
                notif.args.round
            );
        },
  
        onPlayerHandSelectionChanged: function (control_name, item_id) {
            var items = this._playerHand.getSelectedItems();

            if (this.checkAction('attack', true) == false) {
                this._playerHand.unselectAll();
                this.setOpacityOnCards(this._playerHand, '1');
                return;
            }

            // 선택한 카드가 0 장일 때 색 초기화
            if (items.length == 0) {
                this._selectCardValue = 0;
                this.setOpacityOnCards(this._playerHand, '1');
            }
            
            // Attacker 일 때
            if (this._activePlayerRole == 1) {
                if (this._attackCardPlace.count() <= 0) {
                    // 선공 할 때
                    let limit = this._defenderCardsCount >= 5 ? 5 : this._defenderCardsCount;

                    // 선택 한 카드가 5장 이상이거나 수비자 카드보다 작을 때
                    if (items.length > limit) {
                        this._playerHand.unselectItem(item_id);
                    }

                    // 카드 한장을 선택하면 그 카드의 같은 숫자만 낼 수 있게 활성화 함
                    if (items.length > 0) {
                        if (items.length == 1) {
                            var type = this._playerHand.getItemById(item_id).type;
                            this._selectCardValue = this.getCardType(type).value;
                        }

                        // 선택한 카드와 같은 값이 아닌 카드는 투명하게
                        this._playerHand.getAllItems().forEach(card => {
                            if (this.getCardType(card.type).value != this._selectCardValue) {
                                var div = this._playerHand.getItemDivId(card.id);
                                dojo.query('#' + div).style('opacity', '0.5');
                                this._playerHand.unselectItem(card.id);
                            }
                        });
                    }

                } else {
                    // 이후 공격 할 때
                    let limit = this._defenderCardsCount >= 5 ? 5 : this._defenderCardsCount;

                    // 최대 낼 수 있는 값 - 현재 배치된 공격 카드 수 = 낼 수 있는 카드 수
                    if (items.length > limit - this._attackCardPlace.count()) {
                        this._playerHand.unselectItem(item_id);
                    }

                    // 낼 수 있는 카드 업데이트
                    this.updateEnableAttackCards();
                }
            // Defender 일 때 
            } else if (this._activePlayerRole == 2) {
                // 현재 배치된 공격 카드 수 - 현재 배치된 수비 카드 = 낼 수 있는 카드 수
                if (items.length > this._attackCardPlace.count() - this._defenseCardPlace.count()) {
                    this._playerHand.unselectItem(item_id);
                }

                // 방어할 수 있는 카드 업데이트
                this.updateEnableDefenseCards();

            } else if (this._activePlayerRole == 3) {
                // 이후 공격 할 때와 똑같이
                let limit = this._defenderCardsCount >= 5 ? 5 : this._defenderCardsCount;
                // 최대 낼 수 있는 값 - 현재 배치된 공격 카드 수 = 낼 수 있는 카드 수
                if (items.length > limit - this._attackCardPlace.count()) {
                    this._playerHand.unselectItem(item_id);
                }

                // 낼 수 있는 카드 업데이트
                this.updateEnableAttackCards();
            }
        },

        // 배팅 카드
        onPlayerBattingSelectionChanged: function() {
            var items = this._playerToken.getSelectedItems();

            if (items.length > 0) {
                if (this.checkAction('batting')) {

                } else {
                    this._playerToken.unselectAll();
                }
            }
        },

        onClickAttackButton: function() {
            if (this.checkAction('attack', true)) {
                let items = this._playerHand.getSelectedItems();
                
                if (items.length > 0) {
                    let cardsId = [];
                    items.forEach(card => {
                        cardsId.push(card.id);
                    });
                    let data = {
                        cards: cardsId.join(';'),
                    }
                    this.takeAction("attack", data);

                    // 색 초기화
                    this._selectCardValue = 0;
                    this.setOpacityOnCards(this._playerHand, '1');

                    this._playerHand.unselectAll();
                }
            } else {
                this._playerHand.unselectAll();
            }
        },
        
        onClickDefenseButton: function() {
            if (this.checkAction('defense', true)) {
                let items = this._playerHand.getSelectedItems();

                if (items.length <= 0) {
                    this.showMessage(_("Please select a card"), 'error');
                    this._playerHand.unselectAll();
                    return;
                }

                if (items.length < this._attackCardPlace.count() - this._defenseCardPlace.count()) {
                    this.showMessage(_("Not enough cards."), 'error');
                    return;
                }

                var selectCardSum   = [0, 0, 0];
                var attackedCardSum = [0, 0, 0];
                var isDefensed = true;
                var temp = false;
                items.forEach(selectCards => {
                    var selectCardType = this.getCardType(selectCards.type);
                    if (selectCardType.color == this._trumpCardType && selectCardType.value == 10) {
                        temp = true;
                        console.log("asdfasfd");
                    }
                    selectCardSum[selectCardType.color] += selectCardType.value == 10 ? 0 : selectCardType.value;
                });
                for (var i = 0; i < 3; i++) {
                    if (i != this._trumpCardType && (selectCardSum[this._trumpCardType] != 0 || temp == true)) {
                        selectCardSum[i] += 10 + selectCardSum[this._trumpCardType];
                    }
                }

                this._attackedCard.forEach(attackCards => {
                    attackedCardSum[attackCards.type] += Number(attackCards.value);
                });
                for (var i = 0; i < 3; i++) {
                    if (i != this._trumpCardType) {
                        attackedCardSum[i] += attackedCardSum[this._trumpCardType];
                    }
                }

                for (var i = 0; i < 3; i++) {
                    if (selectCardSum[i] < attackedCardSum[i]) {
                        isDefensed = false;
                    }
                }

                console.log(selectCardSum);
                console.log(attackedCardSum);

                if (isDefensed == false) {
                    this.showMessage(_("You cannot defend with this cards"), 'error');
                    return;
                }
                
                if (items.length > 0 && items.length == this._attackCardPlace.count() - this._defenseCardPlace.count()) {
                    let card_ids = [];
                    items.forEach(card => {
                        card_ids.push(card.id);
                    });
                    let data = {
                        cards: card_ids.join(';'),
                    }
                    this.takeAction("defense", data);
                    this._playerHand.unselectAll();
                }
            } else {
                this._playerHand.unselectAll();
            }
        },

        onClickPassButton: function() {
            if (this.checkAction('pass', true)) {
                this.takeAction('pass');
            }
        },

        onClickBattingButton: function(playerId) {
            var items = this._playerToken.getSelectedItems();
            if (items.length <= 0) {
                this.showMessage(_("Please select a betting card"), 'error');
                return;
            }
            
            if (this.checkAction('batting', true)) {
                let data = {
                    card_id: items[0].id,
                    player_id: playerId,
                }
                this.takeAction('batting', data);

                this.gamedatas.playersInfo.forEach(player => {
                    // 플레이어 색상 초기화
                    dojo.query('#blaze-player-' + player.id).attr('data-role', 'none');
                    
                    if (player.id != this.player_id) {
                        this.disconnect($('blaze-player-' + player.id), "onclick",         () => this.onClickBattingButton(player.id));
                        this.disconnect($('blaze-player-' + player.id), "onmouseenter",    () => this.onMouseEnter(player.id));
                        this.disconnect($('blaze-player-' + player.id), "onmouseleave",    () => this.onMouseLeave(player.id));
                    }
                });
            }
        },

        updatePlayer: function(players) {
            // 플레이어 룰 색상 초기화
            players.forEach(player => {
                dojo.query('#blaze-player-' + player.id).attr('data-role', 'none');
            });

            players.forEach(player => {
                if (player.role > 0) {
                    dojo.query('#blaze-player-' + player.id).attr('data-role', (player.role == 1 ? 'attacker' : (player.role == 2 ? 'defender' : 'volunteer')));
                }
            });
        },

        updateEnableAttackCards: function() {
            if (this._attackCardPlace.count() > 0) {
                // 배치된 공격 카드와 수비 카드의 값들을 찾아 활성화 한다.
                var values = new Set();
                this._attackCardPlace.getAllItems().forEach(card => {
                    values.add(this.getCardType(card.type).value);
                });
                this._defenseCardPlace.getAllItems().forEach(card => {
                    values.add(this.getCardType(card.type).value);
                });

                // 배치된 카드와 같은 값이 아닌 카드는 투명하게
                this._playerHand.getAllItems().forEach(card => {
                    if (values.has(this.getCardType(card.type).value) == false) {
                        var div = this._playerHand.getItemDivId(card.id);
                        dojo.query('#' + div).style('opacity', '0.5');
                        this._playerHand.unselectItem(card.id);
                    }
                });

            }
        },

        updateEnableDefenseCards: function() {
            // 선택되지 않은 카드 중 낼 수 있는 카드 활성화
            this._playerHand.getAllItems().forEach(playerCards => {
                var playerCardType = this.getCardType(playerCards.type);

                var disableCards = new Set();
                this._attackedCard.forEach(attackCards => {
                    var attackCardType = {
                        color: attackCards.type,
                        value: attackCards.value
                    }
                    // 타입이 다를 경우
                    if (playerCardType.color != attackCardType.color) {
                        // 내가 들고 있는 카드가 트럼프 카드이면
                        if (playerCardType.color == this._trumpCardType) {
                            // 공격 카드도 트럼프 카드이면
                            if (attackCardType.color == this._trumpCardType) {
                                // 숫자가 낮은 경우 카드 비 활성화
                                if (playerCardType.value <= attackCardType.value || playerCardType.value == 10) {
                                    disableCards.add(playerCards.id);
                                }
                            }
                        } else {
                            disableCards.add(playerCards.id);
                        }
                    }
                });

                this._attackedCard.forEach(attackCards => {
                    var attackCardType = {
                        color: attackCards.type,
                        value: attackCards.value
                    }
                    // 타입이 같고 숫자가 높으면 활성화
                    if (playerCardType.color == attackCardType.color) {
                        if (playerCardType.value >= attackCardType.value) {
                            if (disableCards.has(playerCards.id)) {
                                disableCards.delete(playerCards.id);
                            }
                        } else {
                            disableCards.add(playerCards.id);
                        }
                        if (playerCardType.value == 10) {
                            disableCards.add(playerCards.id);
                        }
                    }
                });

                disableCards.forEach(cardId => {
                    var div = this._playerHand.getItemDivId(cardId);
                    dojo.query('#' + div).style('opacity', '0.5');
                    this._playerHand.unselectItem(cardId);
                });
            });
        },

        roundStart: function(
            playersInfo, 
            currentPlayerHand, 
            currentPlayerToken,
            tokenCards,
            bettingCards,
            bettedCards,
            trumpSuitCards, 
            deckCards, 
            trophyCards,
            trophyCardsOnPlayer,
            round) {
            // 라운드 시작할 때 업데이트 되어야할 목록
            // 1. 트럼프 카드
            console.log(trumpSuitCards);
            this.placeCard(1, 3, trumpSuitCards.type, trumpSuitCards.value);

            // 2. 플레이어가 들고있는 카드
            this._playerHand.removeAll();
            currentPlayerHand.forEach(card => {
                this._playerHand.addToStockWithId(this.getCardUniqueId(card.type, card.value), card.id);
            });
            this._playerToken.removeAll();
            currentPlayerToken.forEach(card => {
                this._playerToken.addToStockWithId((card.type * 2) + Number(card.value), card.id);
            });

            // 3. 배팅한, 배팅된 카드
            playersInfo.forEach(player => {
                // 다른 플레이어 카드
                let playerHand = this._otherPlayerHand.get(player.id);
                playerHand.removeAll();
                for (var i = 0; i < player.handCount; i++) {
                    playerHand.addToStock(0);
                }

                let playerToken = this._otherPlayerToken.get(player.id);
                playerToken.removeAll();

                let playerBettingCard = this._otherplayerBettingCard.get(player.id);
                playerBettingCard.removeAll();
                
                let playerBettedCard = this._otherplayerBettedCard.get(player.id);
                playerBettedCard.removeAll();

                let playerTrophyCard = this._otherplayerTrophyCard.get(player.id);
                playerTrophyCard.removeAll();
            });

            // 4. 다른 플레이어들 카드
            tokenCards.forEach(card => {
                let playerToken = this._otherPlayerToken.get(card.location_arg);
                playerToken.addToStock(0);
            });

            bettingCards.forEach(card => {
                let playerBettingCard = this._otherplayerBettingCard.get(card.location_arg);
                playerBettingCard.addToStock(card.type);
            });

            bettedCards.forEach(card => {
                let playerBettedCard = this._otherplayerBettedCard.get(card.location_arg);
                playerBettedCard.addToStock(card.id);
            });

            this._trophyCard.removeAll();
            trophyCards.forEach(card => {
                if (card.location_arg == round) {
                    this._trophyCard.addToStockWithId(card.value, card.value);
                }
            });

            trophyCardsOnPlayer.forEach(card => {
                let playerTrophyCard = this._otherplayerTrophyCard.get(card.location_arg);
                playerTrophyCard.addToStockWithId(card.value, card.value);
            });
            
            // 5. 덱 카운트
            this.placeText(1, 2, 1, deckCards.length);
        },
    });
});