{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- BlazeBananani implementation : © <Your name here> <Your email address here>
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
    <div id="table">
        <div id="table-container"></div>
    </div>
</div>

<script type="text/javascript">

var jstpl_table = `
<div id="deckCount">\${deckCount}</div>
`

        // <div class="cards" id="decks">
        //     <div class="card" id="testButton"></div>
        // </div>
        // <div id="cards">
        //     <div class="card" id="trumpSuitCard"></div>
        // </div>

var jstpl_cardOnTable = `
<div class="cardOnTable" id="cardOnTable-\${posX}-\${posY}" style="grid-column:\${posX}; grid-row:\${posY}"">
    <div class='card' style="background-position:\${x}px \${y}px"></div>
</div>
`

var jstpl_card = `
<div class=""
`

var jstpl_players = `
<div class="blaze-player" data-pos="\${playerPos}">
    <div class="blaze-player-name" style="color:#\${playerColor}">\${playerName}</div>
    <div>\${playerCardsCount}</div>
</div>
`
// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/
</script>

{OVERALL_GAME_FOOTER}
