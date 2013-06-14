<footer>
	{if ($paginationLimitCount != 0)}
		{$pages = round($paginationCantEntidades/$paginationLimitCount)}
		{$adyacentes = 4}
		{$from = max(1,$paginationCurrentPage-$adyacentes)}
		{$to = min($pages,$paginationCurrentPage+$adyacentes)}
		
		{$showFrom = min((($paginationCurrentPage-1)*$paginationLimitCount)+1,$paginationCantEntidades)}
		{$showTo = min($showFrom+$paginationLimitCount-1,$paginationCantEntidades)}
		
		
		{if isset($smarty.get.count)}
			{$paramCount = "&count=$paginationLimitCount"}
		{/if}

		<div class="pagination-info pull-left">
			Mostrando {$showFrom} al {$showTo} de {$paginationCantEntidades} {if $paginationCantEntidades==1}entrada{else}entradas{/if}
			{if !empty($filtroNombre)}
				(filtro: {$filtroNombre|resaltar:$filtroNombre})
				{$paramFiltroNombre = "&filtroNombre=$filtroNombre"}
			{/if}
		</div>
		
		{if $pages > 1}
			<div class="pagination pagination-mini pull-right">
	 			<ul>
	 				
					{* Anterior *}
	 				{if $paginationCurrentPage > 1}
	 					<li><a pag="{$paginationCurrentPage-1}" count="{$paginationLimitCount}" href="?mod={$modName}&accion={$accion}&pag={$paginationCurrentPage-1}&count={$paginationLimitCount}{$paramFiltroNombre}">&laquo;</a></li>
	 				{else}
						<li class="disabled"><a href="#" >&laquo;</a></li>
					{/if}
					
					{* Primero *}
					{if $from != 1}
						<li>
							<a pag="1" count="{$paginationLimitCount}" href="?mod={$modName}&accion={$accion}&pag=1&count={$paginationLimitCount}{$paramFiltroNombre}">1</a>
						</li>
						<li class="disabled">
							<a href="#">...</a>
						</li>
					{/if}
					
					{* Paginacion *}
					{for $p = $from to $to}
						<li class="{if $p==$paginationCurrentPage || empty($paginationCurrentPage) && $p == 1} active{/if}">
							<a pag="{$p}" count="{$paginationLimitCount}" href="?mod={$modName}&accion={$accion}&pag={$p}&count={$paginationLimitCount}{$paramFiltroNombre}">{$p}</a>
						</li>
					{/for}
					
					{* Ultimo *}
					{if $to != $pages}
						<li class="disabled">
							<a href="#">...</a>
						</li>
						<li>
							<a pag="{$pages}" count="{$paginationLimitCount}" href="?mod={$modName}&accion={$accion}&pag={$pages}&count={$paginationLimitCount}{$paramFiltroNombre}">{$pages}</a>
						</li>
					{/if}
					
					{* Siguiente *}
	 				{if $paginationCurrentPage < $pages}
	 					<li><a pag="{$paginationCurrentPage+1}" count="{$paginationLimitCount}" href="?mod={$modName}&accion={$accion}&pag={$paginationCurrentPage+1}&count={$paginationLimitCount}{$paramFiltroNombre}">&raquo;</a></li>
	 				{else}
						<li class="disabled"><a href="#" >&raquo;</a></li>
					{/if}
					
				</ul>
			</div>
		{/if}
	{/if}
</footer>