define(["dojo", "dojo/_base/declare", "ebg/stock"], (dojo, declare) => {
    return declare("blaze.cardSelectorTrait", null, {
      constructor: function() {
          
      },

      onPlayerHandSelectionChanged: function() {
        var items = this._playerHand.getSelectedItems();
        
        if (items.length > 0) {
            if (this.checkAction('placeCard', true))
            {
                var data = {
                    id : items[0].id,
                }
                this.takeAction("placeCard", data);
            }
        }
      }
    })
});