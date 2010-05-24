{* Smarty * }
<script type="text/javascript">
var cashRegisterStatus = {$cash_register_status};
var documentStatus = {$status};
</script>
<div id="content">
	<div id="frm" class="content_large">
		<fieldset id="cash_register">
			<p><label>Caja Id:</label><span>{$cash_register_id}</span></p>
			<p><label>Fecha:</label><span>{$date}</span></p>
			<p><label>Turno:</label><span>{$shift}</span></p>
			<p><label>Status:</label><span id="cash_register_status">{if $cash_register_status eq 1}Abierto{else}Cerrado{/if}</span></p>
		</fieldset>
		{include file='status_bar_doc_html.tpl'}
		<fieldset id="header_data">
			<p>
				<label>Factura Serie:</label><span id="serial_number">{$serial_number}</span>
			</p>
			<p>
				<label>No:</label><span id="number">{$number}&nbsp;</span>
			</p>
			<p>
				<label>Fecha:</label><span id="date_time">{$date_time}</span>
			</p>
			<p>
				<label>Usuario:</label><span id="username">{$username}</span>
			</p>
		</fieldset>
		<fieldset id="main_data" class="pos disabled">
			<p>
		  		<label id="nit_label">Nit:</label>
		  		<span id="nit">{$nit|htmlchars}&nbsp;</span>
		  		<span id="nit-failed" class="hidden">*</span>
		  	</p>
		  	<p>
		  		<label id="customer_label">Cliente:</label>
		  		<span id="customer">{$customer|htmlchars}&nbsp;</span>
		  		<span id="customer-failed" class="hidden">*</span>
		  	</p>
		  	<object id="bar_code_input"></object>
		  	<div id="details" class="items"></div>
	  	</fieldset>
	  	<fieldset id="data_footer">
	  		<object id="recordset"></object>
	  	</fieldset>
	</div>
</div>