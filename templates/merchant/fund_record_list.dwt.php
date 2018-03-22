<!-- {extends file="ecjia-merchant.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">

</script>
<!-- {/block} -->

<!-- {block name="home-content"} -->
<div class="page-header">
	<div class="pull-left">
		<h3><!-- {if $ur_here}{$ur_here}{/if} --></h3>
  	</div>
	<!-- {if $action_link} -->
	<div class="pull-right">
	  <a class="btn btn-primary data-pjax" href="{$action_link.href}"><i class="fa fa-reply"></i> {t}{$action_link.text}{/t}</a>
	</div>
	<!-- {/if} -->
	<div class="clearfix"></div>
</div>

<div class="row">
	<div class="col-lg-12">
      	<section class="panel">
			<div class="panel-body">
				<section>
					<table class="table table-striped table-hide-edit">
	        			<thead>
	        				<tr class="th-striped">
	        					<th>流水号</th>
	        					<th>提现金额</th>
	        					<th>提现方式</th>
	        					<th>收款账号</th>
	        					<th>申请时间</th>
	        					<th>审核状态</th>
	        				</tr>
	        			</thead>
	        			<tbody>
	        				<!-- {foreach from=$data.item key=key item=list} -->
	        				<tr>
								<td class="hide-edit-area">
									{$list.order_sn}
									<div class="edit-list">
										<a href='{url path="commission/merchant/fund_detail" args="id={$list.id}"}' class="data-pjax" title="查看详情">{t}查看详情{/t}</a>
									</div>
								</td>
								<td>{$list.amount}</td>
								<td></td>
								<td></td>
								<td>{$list.add_time}</td>
								<td>
									{if $list.status eq 1}
										待审核
									{else}
										已审核
									{/if}
								</td>
	        				</tr>
	        				<!-- {foreachelse} -->
	        		    	<tr><td class="dataTables_empty" colspan="6">没有找到任何记录</td></tr>
	        		  		<!-- {/foreach} -->
	        			</tbody>
	        		</table>
				</section>
			</div>
		</section>
      <!-- {$data.page} -->
	</div>
</div>
<!-- {/block} -->