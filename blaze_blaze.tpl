{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Blaze implementation : © <Inhwan Lee> <dlsghks1227@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    blaze_blaze.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div id="board">
    <div id="table" class="blackPanel">
        <div id="playCardText" class="text">{Deck_text}</div>
        <div id="discardCardText" class="text">{Discard_text}</div>

        <div id="playCardOnTable"></div>
        <div id="discardCardOnTable"></div>

        <div id="attackCardsText" class="text">{Attack_Cards_text}</div>
        <div id="attackCardsOnTable">
            <div id="attackCardStock"></div>
        </div>
        <div id="defenseCardsText" class="text">{Defense_Cards_text}</div>
        <div id="defenseCardsOnTable">
            <div id="defenseCardStock"></div>
        </div>
    </div>

    <div id="playerBettingCardsPlace" class="blackPanel" data-state="none">
        <div id="playerBettingCardsStock"></div>
    </div>
    <div id="playerCardsPlace" class="blackPanel">
        <div id="playerCardsStock"></div>
    </div>
    <div id="trophyCardsPlace" class="blackPanel">
        <div id="trophyCardsStock"></div>
    </div>
    
</div>

<script type="text/javascript">
var jstpl_deck = `
<div id="deck" class="blazeCard">
    <div id="cardCount" class="text">x\${count}</div>
</div>
`

var jstpl_trump = `
<div id="trump" class="blazeCard" style="background-position:-\${x}px -\${y}px;">
</div>
`
var jstpl_discard = `
<div id="discard" class="blazeCard" style="background-position:-\${x}px -\${y}px;">
</div>
`

var jstpl_otherPlayer = `
<div id="otherPlayer-\${playerId}" class="otherPlayer" data-pos="\${playerPos}" data-role="none">
    <div id="playerName-\${playerId}" class="playerName text" style="color:#\${playerColor}">\${playerName}</div>
    <div id="playerScore-\${playerId}" class="playerScore text">\${score} Point</div>
    <div id="playCard">
        <div id="otherPlayerCards-\${playerId}" class="otherPlayerCards blazeCard">
            <div id="playCardCount-\${playerId}" class="playCardCount text">x\${playCardCount}</div>
        </div>
    </div>
    <div id="bettingCard">
        <div id="otherPlayerBettingCards-\${playerId}" class="otherPlayerBettingCards blazeCard" style="background-position:-\${playerCardColor}px 0px;">
            <div id="bettingCardCount-\${playerId}" class="bettingCardCount text">x\${bettingCardCount}</div>
        </div>
    </div>
</div>
`

var jstpl_overallCards = `
<div id="overallCards-\${playerId}" class="overallCards">
    <div id="overallBettingCardPlace">
        <div id="overallBettingCardStock-\${playerId}" class="overallStock"></div>
    </div>
    <div id="overallBettedCardPlace">
        <div id="overallBettedCardStock-\${playerId}" class="overallStock"></div>
    </div>
    <div id="overallTrophyCardPlace">
        <div id="overallTrophyCardStock-\${playerId}" class="overallStock"></div>
    </div>
</div>
`

</script>  

{OVERALL_GAME_FOOTER}