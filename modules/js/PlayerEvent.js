define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.playerEvent", null, {
        constructor: function() {
            this._notifications.push(
                ["attack", 1200],
                ["defense", 1200],
            );

            this._firstAttackCardValue = -1;

            this._invaildAttackCardOnTable = [];
            this._selectedDefenseCardId = -1;
        },

        /*
         * Player hand
         */
        onPlayerCardSelectionChanged: function(controlName, itemId) {
            var selectedItems = this._playerCardStock.getSelectedItems();
            this._invaildPlayerCards = [];

            if (selectedItems.length <= 0) {
                this.resetSelectedCardsInStock(this._playerCardStock);
                this.resetCombinedDefenseCards();

                if (this._attackCardsOnTable.length > 0 && (this._activePlayerRole == "1" || this._activePlayerRole == "3")) {
                    this._invaildPlayerCards = this.updateInvaildPlayerCard();
                }

                this._firstAttackCardValue = -1;

                this._attackCardStock.unselectAll();
                this._selectedDefenseCardId = -1;
                
                return;
            }

            if (this._activePlayerRole == "1") {
                if (this._attackCardsOnTable.length <= 0) {
                    this._invaildPlayerCards = this.playAttack(selectedItems, controlName);
                } else {
                    this._invaildPlayerCards = this.updateInvaildPlayerCard();
                }

            } else if (this._activePlayerRole == "2") {
                this._selectedDefenseCardId = itemId;

                if (this._combinedDefenseCards.length > 0) {
                    var idx = this._combinedDefenseCards.findIndex(function(card) {
                        return card.defenseCardId == itemId;
                    });
                    if (idx > -1) {
                        this._combinedDefenseCards.splice(idx, 1);
                    }
                }

                this.updateInvalidAttackCardOnTable(itemId);

            } else if (this._activePlayerRole == "3") {
                this._invaildPlayerCards = this.updateInvaildPlayerCard();
            }

            this._invaildPlayerCards.forEach(cardId => {
                if (itemId == cardId) {
                    this._playerCardStock.unselectItem(itemId);
                }
            });
        },

        onAttackCardSelectionChanged: function(controlName, itemId) {
            var selectedItems = this._attackCardStock.getSelectedItems();

            if (this._selectedDefenseCardId == -1) {
                this._attackCardStock.unselectAll();
                return;
            }

            var vaildSelected = true;
            this._invaildAttackCardOnTable.forEach(cardId => {
                if (itemId == cardId) {
                    this._attackCardStock.unselectItem(itemId);
                    vaildSelected = false;
                }
            });
            
            if (selectedItems.length == 1 && this._selectedDefenseCardId >= 0 && vaildSelected == true) {
                if (this._activePlayerRole == "2") {
                    this._combinedDefenseCards.push({
                        defenseCardId: this._selectedDefenseCardId,
                        attackCardId: itemId
                    });
                    this.resetCombinedDefenseCards();

                    this._playerCardStock.unselectAll();
                    this._attackCardStock.unselectAll();
                }
            }
        },

        /*
         * Buttons
         */
        onClickAttackButton: function() {
            if (this.checkAction("attack", true)) {
                var selectedItem = this._playerCardStock.getSelectedItems();

                if (selectedItem.length > 0) {
                    var ids = [];

                    selectedItem.forEach(card => {
                        ids.push(card.id);
                    });
        
                    var requestData = {
                        cards_id : ids.join(";")
                    };

                    this.takeAction("attack", requestData);
                }
            }
        },

        onClickDefenseButton: function() {
            if (this.checkAction("defense", true)) {
                if (this._combinedDefenseCards.length > 0) {
                    var defenseCardsId = [];
                    var attackCardsId = [];

                    this._combinedDefenseCards.forEach(card => {
                        defenseCardsId.push(card.defenseCardId);
                        attackCardsId.push(card.attackCardId);
                    });

                    var requestData = {
                        defense_cards_id : defenseCardsId.join(";"),
                        attack_cards_id : attackCardsId.join(";"),
                    };

                    this.takeAction("defense", requestData);
                }
            }
        },

        onClickPassButton: function() {
            if (this.checkAction("pass", true)) {
                this.takeAction('pass');
            }
        },

        /*
         *  Utility
         */
        getRequestCardsData: function(item) {
            var ids = [];

            item.forEach(card => {
                ids.push(card.id);
            });

            return {
                cards_id : ids.join(";")
            };
        },

        resetSelectedCardsInStock: function(stock) {
            stock.getAllItems().forEach(item => {
                var cardDiv = stock.getItemDivId(item.id);
                dojo.removeClass(cardDiv, 'disableCard');
                dojo.attr(cardDiv, "data-color", -1);
            });
        },

        setEnableCard: function(controlName, cardId, isEnable) {
            // dojo.addClass()
            if (isEnable == false) {
                dojo.addClass(controlName + "_item_" + cardId, 'disableCard');
            } else {
                dojo.removeClass(controlName + "_item_" + cardId, 'disableCard');
            }
        },

        playAttack: function(selectedCards, controlName) {
            var invalidCards = [];

            selectedCards.forEach(card => {
                var selectedCardData = this.getCardDataWithType(card.type);
                if (selectedCards.length <= 1) {
                    this._firstAttackCardValue = selectedCardData.value;
                }
            });

            if (this._firstAttackCardValue >= 0) {
                var allCards = this._playerCardStock.getAllItems();
                allCards.forEach(card => {
                    var cardData = this.getCardDataWithType(card.type);
                    if (this._firstAttackCardValue != cardData.value) {
                        this.setEnableCard(controlName, card.id, false);
                        invalidCards.push(card.id);
                    }
                });
            }

            return invalidCards;
        },

        updateInvaildPlayerCard: function() {
            var invalidCards = [];

            var allCards = this._playerCardStock.getAllItems();
            allCards.forEach(card => {
                var cardData = this.getCardDataWithType(card.type);

                var isValid = false;
                this._attackCardsOnTable.forEach(card => {
                    if (cardData.value == card.value) {
                        isValid = true;
                    }
                });

                if (isValid == false) {
                    this.setEnableCard("playerCardsStock", card.id, false);
                    invalidCards.push(card.id);
                }
            });

            return invalidCards;
        },

        updateInvalidAttackCardOnTable: function(selectedDefenseCardId) {
            var invalidCards = [];
            
            this.resetCombinedDefenseCards();
            
            var allCards = this._attackCardStock.getAllItems();
            allCards.forEach(attackCard => {
                if (this._combinedDefenseCards.length > 0) {
                    this._combinedDefenseCards.forEach(combinedCard => {
                        if (combinedCard.attackCardId == attackCard.id) {
                            invalidCards.push(attackCard.id);
                        }
                    });
                }

                if (this.isCombineValidated(selectedDefenseCardId, attackCard.id) == false) {
                    if (invalidCards.includes(attackCard.id) == false) {
                        invalidCards.push(attackCard.id);
                    }
                }
            });

            invalidCards.forEach(cardId => {
                this.setEnableCard("attackCardStock", cardId, false);
            });
        },

        resetCombinedDefenseCards: function() {
            this.resetSelectedCardsInStock(this._playerCardStock);
            this.resetSelectedCardsInStock(this._attackCardStock);

            if (this._combinedDefenseCards.length > 0) {
                this._combinedDefenseCards.forEach((combinedCard, index) => {
                    dojo.addClass("playerCardsStock_item_" + combinedCard.defenseCardId, 'disableCard');
                    dojo.attr("playerCardsStock_item_" + combinedCard.defenseCardId, "data-color", index);

                    dojo.addClass("attackCardStock_item_" + combinedCard.attackCardId, 'disableCard');
                    dojo.attr("attackCardStock_item_" + combinedCard.attackCardId, "data-color", index);
                });
            }
        },

        isCombineValidated: function(defenseCardId, attackCardId) {
            var defenseCardType = this._playerCardStock.getItemById(defenseCardId).type;
            var attackCardType = this._attackCardStock.getItemById(attackCardId).type;
            
            var defenseCardData = this.getCardDataWithType(defenseCardType);
            var attackCardData = this.getCardDataWithType(attackCardType);

            if (attackCardData.color != defenseCardData.color) {
                if (defenseCardData.color != this._trumpCard.color) {
                    return false;
                }
            } else {
                if (attackCardData.value > defenseCardData.value || defenseCardData.value == 10) {
                    return false;
                }
            }

            return true;
        },
    });
});