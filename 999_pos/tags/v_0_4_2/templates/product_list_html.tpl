{* Smarty *}
<div id="content">
	<table id="list" class="content_small">
		{include file='list_caption_html.tpl'}
		<thead>
			<tr>
				<th>Nombre</th>
				<th>Casa</th>
			</tr>
		</thead>
		<tbody>
		{section name=i loop=$list}
			<tr>
				<td>
					<a href="{$item_link|cat:$list[i].id|cat:'&last_cmd='|cat:$actual_cmd|cat:'&page='|cat:$page}"
						onclick="oSession.setIsLink(true);">
						{$list[i].name|escape|wordwrap:76:"<br />":true}
					</a>
				</td>
				<td>{$list[i].manufacturer|escape|wordwrap:38:"<br />":true}</td>
			</tr>
		{/section}
		</tbody>
	</table>
</div>