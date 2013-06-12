<footer>
	{if ($paginationLimitCount != 0)}
		{$pages = round($paginationCantEntidades/$paginationLimitCount)}
		{if $pages > 1}
			<div class="pagination pagination-small pagination-right">
	 			<ul>
	 				<li><a {if $paginationCurrentPage>1} href="?mod={$modName}&accion={$accion}&pag={$paginationCurrentPage-1}&count={$paginationLimitCount}"  {else}class="disabled"{/if}>&laquo;</a></li>
					{for $p = 1 to $pages }
						<li class="{if $p==$paginationCurrentPage || empty($paginationCurrentPage) && $p == 1} active{/if}">
							<a href="?mod={$modName}&accion={$accion}&pag={$p}&count={$paginationLimitCount}">{$p}</a>
						</li>
					{/for}
					<li><a href="#">&raquo;</a></li>
				</ul>
			</div>
		{/if}
	{/if}
</footer>