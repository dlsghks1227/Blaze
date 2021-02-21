{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- BlazeBananani implementation : © <Inhwan Lee> <dlsghks1227@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    blazebananani_blazebananani.tpl
    
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
    <div id="hand">
        <div id="hand-cards"></div>
    </div>
    <div id="battingHand">
        <div id="batting-cards"></div>
    </div>

    <div id="discard">
        <div id="discard-cards"></div>
    </div>

    <div id="trophy">
        <div id="trophy-cards"></div>
    </div>

    <div id="table">
        <div id="table-container">
            <div class="textOnTable" id="textOnTable-2-2" style="grid-column:2 / span 5; grid-row:2">
                <div class="text">Attack Cards</div>
            </div>
            <div class="textOnTable" id="textOnTable-2-4" style="grid-column:2 / span 5; grid-row:4">
                <div class="text">Defense Cards</div>
            </div>
            <div class="cardOnTable" id="deckOnTable" style="margin-left: 10px; grid-column:1 / span 6; grid-row:1"></div>
            <div class="cardOnTable" id="attackCardOnTable" style="grid-column:2 / span 5; grid-row:1"></div>
            <div class="cardOnTable" id="defenseCardOnTable" style="grid-column:2 / span 5; grid-row:3"></div>
        </div>
    </div>
</div>

<script type="text/javascript">

var jstpl_overallCards = `
<div class="overallCards" id="overallCards-\${playerId}">
    <div id="minibetting">
        <div class="cards" id="player-mini-betting-cards-\${playerId}"></div>
    </div>
    <div id="miniBetted">
        <div class="cards" id="player-mini-betted-cards-\${playerId}"></div>
    </div>
    <div id="miniTrophy">
        <div class="cards" id="player-mini-trophy-cards-\${playerId}"></div>
    </div>
</div>
`

var jstpl_cardOnTable = `
<div class="cardOnTable" id="cardOnTable-\${posX}-\${posY}" style="grid-column:\${posX}; grid-row:\${posY}"">
    <div class='card' data-filp='\${filp}' style="background-position:-\${x}px -\${y}px"></div>
</div>
`

var jstpl_textOnTable = `
<div class="textOnTable" id="textOnTable-\${posX}-\${posY}" style="grid-column:\${posX} / span \${size}; grid-row:\${posY}"">
    <div class="text">\${text}</div>
</div>
`

var jstpl_players = `
<div class="blaze-player" id="blaze-player-\${playerId}" data-pos="\${playerPos}">
    <div id="player-container">
        <div class="blaze-player-name" style="color:#\${playerColor}">\${playerName}</div>
        <div class="player-cards">
            <div class="cards" id="player-cards-\${playerId}"></div>
        </div>
        <div class="player-token-cards">
            <div class="cards" id="player-token-cards-\${playerId}"></div>
        </div>
    </div>
</div>
`
// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/
</script>

{OVERALL_GAME_FOOTER}
