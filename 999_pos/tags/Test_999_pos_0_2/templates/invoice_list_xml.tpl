{* Smarty *}
{php}
header('Content-Type: text/xml');
{/php}
<?xml version="1.0" encoding="UTF-8"?>
<response>
	<success>1</success>
	<grid>
		{section name=i loop=$list}
		<row>
			<id>{$list[i].id}</id>
			<serial_number>{$list[i].serial_number}</serial_number>
			<number>{$list[i].number}</number>
		</row>
		{/section}
	</grid>
</response>