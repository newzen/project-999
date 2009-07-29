{* Smarty * }
{config_load file="site.conf"}
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>{#site_title#} - {$module_title}</title>
<link href="../styles/layout.css" rel="stylesheet" type="text/css" />
<link href="../styles/typography.css" rel="stylesheet" type="text/css" />
<link href="../styles/decoration.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/session.js"></script>
{literal}
<script type="text/javascript">
	var oSession = new Session();
	window.onbeforeunload = function(oEvent){
		oSession.verifyStatus(oEvent);
	}
	document.oncontextmenu = function(){
		return false;
	}
</script>
{/literal}
</head>
<body>
	<div id="wrapper">
		<div id="header">
			<h1>Sistema 999 - {$module_title}</h1>
			<h3>
			{foreach from=$back_trace item=trace name=back_trace_loop}
				{$trace}
				{if not $smarty.foreach.back_trace_loop.last} >> {/if}
			{/foreach}
			</h3>
		</div>
		<div id="main_menu">
			{include file=$main_menu}
		</div>
		<div id="console">
		{if $notify eq 1}
			<p class="{$type}">{$message}</p>
		{/if}
		</div>
		<div id="second_menu">
			{include file=$second_menu}
		</div>
		<div id="content">
			{include file=$content}
		</div>
	</div>
</body>
</html>