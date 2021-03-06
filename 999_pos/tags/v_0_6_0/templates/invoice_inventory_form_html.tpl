{* Smarty *}
{* status = 0 Edit, status = 1 Idle, status = 2 Cancelled *}
<script type="text/javascript" src="../scripts/core_libs.js"></script>
<script type="text/javascript" src="../scripts/form_libs.js"></script>
<script type="text/javascript" src="../scripts/event_delegator.js"></script>
<script type="text/javascript" src="../scripts/details.js"></script>
<script type="text/javascript" src="../scripts/object_page.js"></script>
<script type="text/javascript" src="../scripts/invoice_page.js"></script>
<script type="text/javascript">
	var oConsole = new Console('console');
	var oMachine = new StateMachine({$status});
	var oRemoveObject = new RemoveSessionObjectCommand(oSession, oConsole, Request.createXmlHttpRequestObject(), {$key});
	var oEventDelegator = new EventDelegator();
	oEventDelegator.init();
	var oDetails = new InvoicePage(oSession, oConsole, Request.createXmlHttpRequestObject(), {$key}, oMachine, oEventDelegator);
	{literal}
	window.onunload = function(){
		oRemoveObject.execute();
	}
	{/literal}
</script>
<div id="content">
	<div id="frm" class="content_large">
		{include file='status_bar_invoice_html.tpl'}
		{include file='header_data_invoice_html.tpl'}
		<fieldset id="main_data">
		  	<p>
		  		<label>Nit:</label>
		  		<span>{$nit}</span>
		  	</p>
		  	<p>
		  		<label>Nombre:</label>
		  		<span>{$customer|escape}&nbsp;</span>
		  	</p>
	  		{* Because Firefox css rule margin-top on table rule bug. *}
	  		<p>&nbsp;</p>
		  	<div id="details" class="items"></div>
		</fieldset>
		<fieldset>&nbsp;</fieldset>
	</div>
</div>
<script type="text/javascript">
oDetails.init('../xsl/invoice_page.xsl', 'details', 'oDetails');
oDetails.getLastPage();
</script>