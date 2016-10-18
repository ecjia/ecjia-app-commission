<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->
<!-- {block name="footer"} -->
<script type="text/javascript">

</script>
<!-- {/block} -->
<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
	</h3>
</div>

<ul class="nav nav-pills">
    <li class="{if !$smarty.get.type}active{/if}">
		<a class="data-pjax" href="{RC_Uri::url('commission/admin/init')}">全部
			<span class="badge badge-info">{$bill_list.filter.count_all}</span>
		</a>
	</li>
	<li class="{if $smarty.get.type eq 1}active{/if}">
		<a class="data-pjax" href="{RC_Uri::url('commission/admin/init', 'type=1')}">未结算 
			<span class="badge badge-info">{$bill_list.filter.count_unpay}</span>
		</a>
	</li>
	<li class="{if $smarty.get.type eq 2}active{/if}">
		<a class="data-pjax" href="{RC_Uri::url('commission/admin/init', 'type=2')}">部分结算 
			<span class="badge badge-info">{$bill_list.filter.count_paying}</span>
		</a>
	</li>
	<li class="{if $smarty.get.type eq 3}active{/if}">
		<a class="data-pjax" href='{RC_Uri::url("commission/admin/init", "type=3")}'>已结算
			<span class="badge badge-info use-plugins-num">{$bill_list.filter.count_payed}</span>
		</a>
	</li>
	
</ul>

<!-- 批量操作 -->
<div class="row-fluid batch" >
	<form method="post" action="{$search_action}" name="searchForm">
		<div class="btn-group f_l m_r5">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
				<i class="fontello-icon-cog"></i>{t}批量操作{/t}
				<span class="caret"></span>
			</a>
			<ul class="dropdown-menu">
				<li><a class="button_remove" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{url path='store/admin_commission/batch'}" data-msg="您确定要删除选中的佣金结算吗？" data-noSelectMsg="请先选中要删除的佣金结算！" data-name="id" href="javascript:;"><i class="fontello-icon-trash"></i>{t}删除佣金结算{/t}</a></li>
			</ul>
		</div>
	</form>
</div>

<div class="row-fluid list-page">
	<div class="span12">
		<div class="tab-content">
			<!-- system start -->
			<div class="row-fluid">
				<table class="table table-striped smpl_tbl dataTable table-hide-edit">
					<thead>
						<tr >
							<th class="table_checkbox"><input type="checkbox" name="select_rows" data-toggle="selectall" data-children=".checkbox"/></th>
						    <th>{t}账单编号{/t}</th>
						    <th>{t}商家名称{/t}</th>
						    <th>{t}订单有效总金额{/t}</th>
						    <th>{t}订单退款总金额{/t}</th>
						    <th>{t}佣金比例{/t}</th>
						    <th>{t}商家有效佣金{/t}</th>
						 </tr>
					</thead>

   				 <!-- {foreach from=$bill_list.item item=commission} -->
						<tr>
							<td><span><input type="checkbox" name="checkboxes[]" class="checkbox" value="{$commission.id}"/></span></td>
							<td>
							<a class="data-pjax" href='{RC_Uri::url("commission/admin/detail","id={$commission.bill_id}")}' title="账单详情">
								{$commission.bill_sn}
								</a>
								<!-- <div class="edit-list">
      								<a class="data-pjax" href='{RC_Uri::url("store/admin_commission/order_list","store_id={$commission.store_id}")}' title="订单列表">{t}订单列表{/t}</a>&nbsp;|&nbsp;
      								<a class="data-pjax" href='{RC_Uri::url("store/admin_commission/edit","id={$commission.id}&store_id={$commission.store_id}")}' title="编辑">{t}编辑{/t}</a>&nbsp;|&nbsp;
      								<a data-toggle="ajaxremove" class="ajaxremove ecjiafc-red" data-msg="{t}您确定要删除吗？{/t}" href='{RC_Uri::url("store/admin_commission/remove","id={$commission.id}")}' title="删除">{t}删除{/t}</a>
								</div> -->
							</td>
						    <td> {assign var=store_url value=RC_Uri::url('store/admin/preview',"store_id={$commission.store_id}")}
        					     <a href="{$store_url}" target="_blank" class="ecjiafc-red">{$commission.merchants_name}</a></td>
						    <td>￥{$commission.order_amount}</td>
						    <td>￥{$commission.refund_amount}</td>
						    <!-- {if $commission.percent_value} -->
						    <td>{$commission.percent_value}%</td>
						    <!-- {else} -->
						    <td>{t}0{/t}</td>
						    <!-- {/if} -->
						    <td>￥{$commission.bill_amount}</td>
						</tr>
						<!-- {foreachelse} -->
					   <tr><td class="no-records" colspan="7">{t}没有找到任何记录{/t}</td></tr>
					<!-- {/foreach} -->
				</table>
				<!-- {$bill_list.page} -->
			</div>
		</div>
	</div>
</div> 
<!-- {/block} -->