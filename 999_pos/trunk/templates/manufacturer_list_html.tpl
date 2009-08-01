{* Smarty *}
<div id="list_results">
	{if $total_items eq 0}
		<p>No hay resultados.</p>
	{else}
		<table>
			<caption>
				<span>{$first_item} - {$last_item} de {$total_items}</span>
				<span>
					{if $previous_link neq ''}
						<a href="{$previous_link}" onclick="oSession.setIsLink(true);">Anterior</a>
					{else}
						Anterior
					{/if} |
					{if $next_link neq ''}
						<a href="{$next_link}" onclick="oSession.setIsLink(true);">Siguiente</a>
					{else}
						Siguiente
					{/if}
				</span>
				<span>P&aacute;gina {$page} de {$total_pages}</span>
			</caption>
			<thead>
				<tr>
					<th>Nombre</th>
				</tr>
			</thead>
			<tbody>
			{section name=i loop=$list}
				<tr>
					<td><a href="{$item_link|cat:$list[i].manufacturer_id}"
							onclick="oSession.setIsLink(true);">{$list[i].name}</a></td>
				</tr>
			{/section}
			</tbody>
		</table>
	{/if}
</div>