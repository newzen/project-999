/**
 * @fileOverview Library with the ToolbarDate class.
 * @author Roberto Oliveros
 */

/**
 * @class Represents a date input element in the toolbar.
 * @extends ToolbarText
 * @constructor
 */
function ToolbarDate(){
	// Call parents constructor.
	ToolbarText.call(this);
}

/**
 * Inherit the parent's class methods.
 */
ToolbarDate.prototype = new ToolbarText();

/**
 * Sets the text input widget and the next one too.
 * @param {String} sWidget The id of the input element.
 * @param {String} sNextWidget The id of the next input element.
 */
ToolbarDate.prototype.init = function(sWidget, sNextWidget){
	// Call parents init method first.
	ToolbarText.prototype.init.call(this, sWidget, sNextWidget);
	
	var oTemp = this;
	this._mWidget.onkeyup = function(oEvent){
		oTemp.handleKeyUp(oEvent);
	}
}

/**
 * Handles the key up press event. If all the input spaces are filled, it moves the focus to the next input.
 * @param {Event} oEvent
 */
ToolbarDate.prototype.handleKeyUp = function(oEvent){
 	oEvent = (!oEvent) ? window.event : oEvent;
 	var code = (oEvent.keyCode) ? oEvent.keyCode :
 			((oEvent.which) ? oEvent.which : 0);
 	
 	if(code != 8 && code != 46){
 		var oWidget = this._mWidget;
 		
 		switch(oWidget.value.length){
 			case 2:
 				var oReg = /\d{2}/;
 	 			if(oReg.test(oWidget.value))
 	 				oWidget.value += '/';
 	 			break;
 	 			
 			case 5:
 				var oReg = /\d{2}\/\d{2}/;
 	 			if(oReg.test(oWidget.value))
 	 				oWidget.value += '/';
 	 			break;
 	 			
 			case 10:
 				// Codes order: tab, shift, end, home, arrows left, up, right, down.
 				if(code != 9 && code != 13 && code != 16 && code != 35 && code != 36 && code != 37 &&
 						code != 38 && code != 39 && code != 40)
 					StateMachine.setFocus(this._mNextWidget);
 				
 			default:
 		}
 	}
 		
}