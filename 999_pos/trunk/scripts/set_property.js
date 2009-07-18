/**
 * Library with the SetProperty command class.
 * @package Client
 * @author Roberto Oliveros
 */

/**
 * Constructor function.
 * @param Console oConsole
 * @param string sKey
 */
function SetPropertyCommand(oConsole, sKey){
	// Call the parent constructor.
	Command.call(oConsole);
	
	/**
	 * Holds the key of the session object.
	 */
	this.key = sKey;
	
	/**
	 * Queue for storing the future requests in case the request object is busy.
	 * @var Array
	 */
	this.requestQueue = new Array();
}

/**
 * Executes the command.
 * @param string sCmd
 * @param string sValue
 * @param string sElementId
 */
SetPropertyCommand.prototype.execute = function(sCmd, sValue, sElementId){
	if(sCmd == '' || sElementId == '')
		this.console.displayError('Interno: Argumentos sCmd y sElementId inv&aacute;lidos.');
	else{
		var str = Url.addUrlParam(Url.getUrl(), 'cmd', sCmd);
		str = Url.addUrlParam(str, 'key', this.key);
		str = Url.addUrlParam(str, 'value', sValue);
		str = Url.addUrlParam(str, 'elementid', sElementId);
		this.requestQueue.push(str);
		this.sendParams();
	}
}

/**
 * Send the values to the server.
 */
SetPropertyCommand.prototype.sendRequest = function(){
	// Continue only if the request is not busy or the queue is not empty.
	if((this.request.readyState == 4 || this.request.readyState == 0) && this.requestQueue.lenght > 0){
		var queueEntry = this.requestQueue.shift();
		var urlParams = Url.addUrlParam(queueEntry, 'type', 'xml');
		this.request.open('GET', urlParams, true);
		this.request.onreadystatechange = this.handleRequestStateChange;
		this.request.send(null);
	}
}