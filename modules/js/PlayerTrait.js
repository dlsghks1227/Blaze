define(["dojo", "dojo/_base/declare"], (dojo, declare) => {
    return declare("blaze.playerTrait", null, {
        constructor: function() {
            this._notifications.push(
                // ["", 1200],
            );

            this._playerCardStock = new ebg.stock();
            this._playerBettingCardStock = new ebg.stock();
        },

        /*
         *  Stock
         */
        createPlayerCardStock: function() {
            this._playerCardStock.create(this, $("playerCardsStock"), this._CARD_WIDTH_L, this._CARD_HEIGHT_L);
            this._playerCardStock.image_items_per_row = 10;
            this._playerCardStock.centerItems = true;
            this._playerCardStock.setOverlap(70, 0);
            this._playerCardStock.setSelectionMode(2);
            this._playerCardStock.setSelectionAppearance("class");
            this._playerCardStock.extraClasses = "blazeCard";

            for (var color = 0; color <= 2; color++) {
                for (var value = 1; value <= 10; value++) {
                    var uniqueId = this.getCardUniqueId(color, value);
                    this._playerCardStock.addItemType(uniqueId, uniqueId, g_gamethemeurl + "img/play_cards_L.png", uniqueId);
                }
            }

            dojo.connect(this._playerCardStock, "onChangeSelection", this, "onPlayerCardSelectionChanged");
        },
        
        createPlayerBettingCardStock: function() {
            this._playerBettingCardStock.create(this, $("playerBettingCardsStock"), this._CARD_WIDTH_L, this._CARD_HEIGHT_L);
            this._playerBettingCardStock.image_items_per_row = 2;
            this._playerBettingCardStock.centerItems = true;
            this._playerBettingCardStock.setOverlap(60, 0);
            this._playerBettingCardStock.setSelectionMode(0);
            this._playerBettingCardStock.setSelectionAppearance("class");
            this._playerBettingCardStock.extraClasses = "blazeCard";

            for (var color = 0; color <= 4; color++) {
                for (var value = 0; value <= 1; value++) {
                    var uniqueId = this.getBettingCardUniqueId(color, value);
                    this._playerBettingCardStock.addItemType(uniqueId, uniqueId, g_gamethemeurl + "img/betting_cards_L.png", uniqueId);
                }        
            }
        },

        /*
         *  Setup player
         */
        getPlayerPlaceReorder: function(currentPlayerId, nextPlayerTable) {
            // 자신 이외의 플레이어 위치 및 카드 설정
            // 자신이 남쪽 방향(6시 방향) 고정이고 자기 다음 플레이어가 왼쪽으로 가야하므로 자신 플레이어 기준으로 배열을 다시 생성한다.
            // this.player_id : 자신의 플레이어 고유 아이디 반환
            var table = new Map();
            table.set(currentPlayerId, 1);

            var placePos = 2;
            for (var nextPlayerId = nextPlayerTable[currentPlayerId]; nextPlayerId != currentPlayerId; nextPlayerId = nextPlayerTable[nextPlayerId]) {
                table.set(nextPlayerId, placePos);
                placePos++;
            }

            return table;
        },

        setupCurrentPlayerCards: function(currentPlayerData) {
            this._playerCardStock.removeAll();
            currentPlayerData.hand.forEach(card => {
                var uniqueId = this.getCardUniqueId(Number(card.color), Number(card.value));
                this._playerCardStock.addToStockWithId(uniqueId, card.id);
            });

            this._playerBettingCardStock.removeAll();
            currentPlayerData.bettingHand.forEach(card => {
                var uniqueId = this.getBettingCardUniqueId(Number(card.color), Number(card.value));
                this._playerBettingCardStock.addToStockWithId(uniqueId, card.id);
            });
        },

        setupPlayersPlace: function(players, currentPlayerId, playerPlace, isStockSetup = true) {
            // 모든 플레이어를 반복하면서 위치를 설정한다.
            players.forEach(player => {
                var isCurrentPlayer = player.id == currentPlayerId;
                
                // 플레이어 손 안에 있는 카드
                //      - 자신이 아니라면 카드 수를 반환한다.
                //      - 보드에 올려진 내 카드 수를 보여주기 위해 변수 선언
                var playerPlayCardCount     = isCurrentPlayer ? player.hand.length : player.hand;
                var playerBettingCardCount  = isCurrentPlayer ? player.bettingHand.length : player.bettingHand;

                if (isCurrentPlayer == true) {
                    this.setupCurrentPlayerCards(player);
                }

                this.placeOtherPlayer(
                    player.id, 
                    player.name, 
                    player.color,
                    playerPlace.get(Number(player.id)),
                    this._CARD_WIDTH_M * (Number(player.no) - 1),
                    player.score,
                    playerPlayCardCount,
                    playerBettingCardCount);

                this.updateOtherPlayerRole(player.id, player.role);

                if (isStockSetup == true) {
                    this.setupOverallCardStock(player.id);
                }
            });
        },

        /*
         *  Other player update
         */
        placeOtherPlayer: function(playerId, playerName, playerColor, playerPos, playerCardColor, score, playCardCount, bettingCardCount) {
            if ($("otherPlayer-" + playerId)) {
                dojo.destroy("otherPlayer-" + playerId);
            }

            dojo.place(this.format_block("jstpl_otherPlayer", {
                playerId:           playerId,
                playerName:         playerName,
                playerColor:        playerColor,
                playerPos:          playerPos,
                playerCardColor:    playerCardColor,
                score:              score,
                playCardCount:      playCardCount,
                bettingCardCount:   bettingCardCount,
            }), "board");
        },

        updateOtherPlayerRole: function(playerId, role) {
            dojo.query("#otherPlayer-" + playerId).attr("data-role", role);
        },

        updateOtherPlayerPlayCardCount: function(playerId, count) {
            $("playCardCount-" + playerId).innerHTML = "x" + count;
        },

        updateOtherPlayerBettingCardCount: function(playerId, count) {
            $("bettingCardCount-" + playerId).innerHTML = "x" + count;
        },

        updateOtherPlayerScore: function(playerId, score) {
            $("playerScore-" + playerId).innerHTML = score + " Point";
        },
    });
});