<?php
/**
 * 账单
 */
defined('IN_ECJIA') or exit('No permission resources.');
class store_bill_detail_model extends Component_Model_Model {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->table_name = 'store_bill_detail';
		parent::__construct();

	}
	/*
	 * brokerage_amount 订单入账时需校验金额是否和订单一致，退货为负数须校验该订单总退货次数所有金额是否超过订单金额
	 * percent_value 根据storeid获取，退货获取订单入账时比例
	 * TODO：订单入账时需校验金额是否和订单一致，退货为负数须校验该订单总退货次数所有金额是否超过订单金额
	 * TODO：异常处理，记录
	 */

	public function add_bill_detail($data) {
        if (!is_array($data) || !isset($data['order_type']) || !isset($data['order_id']) ) {
            RC_Logger::getLogger('bill_order')->error($data);
            return false;
        }

        $order_info = RC_DB::table('order_info')->where('order_id', $data['order_id'])->first();
        if (empty($order_info)) {
            RC_Logger::getLogger('bill_order')->error($data);
            return false;
        }
        if(!isset($data['store_id'])) {
            $data['store_id'] = $order_info['store_id'];
        }
        if( !isset($data['order_amount'])) {
            $data['order_amount'] = $order_info['order_amount'];
        }
        if ($data['order_type'] == 1) {
            $data['percent_value'] = RC_Model::model('commission/store_franchisee_model')->get_store_commission_percent($data['store_id']);
            if (empty($data['percent_value'])) {
                $data['percent_value'] = 100; //未设置分成比例，默认100
            }
            $data['brokerage_amount'] = $data['order_amount'] * $data['percent_value'] / 100;
        } else if ($data['order_type'] == 2) {
            //退货时 结算比例使用当时入账比例
            $data['percent_value'] = $this->get_bill_percent($data['order_id']);
            if (!$data['percent_value']) {
                RC_Logger::getLogger('bill_order')->error('退货未找到原入账订单，订单号：'.$data['order_id']);
                RC_Logger::getLogger('bill_order')->error($data);
                return false;
            }
            if (($data['brokerage_amount'] = $data['order_amount'] * $data['percent_value'] / 100) > 0) {
                $data['brokerage_amount'] *= -1;
            }
        }

        $data['add_time'] = RC_Time::gmtime();
        RC_Logger::getLogger('bill_order')->info($data);
        unset($data['order_amount']);
	    return RC_DB::table('store_bill_detail')->insertGetId($data);
	}

	//计算日账单,分批处理数据
	public function count_bill_day($options) {
	    $table = RC_DB::table('store_bill_detail')->groupBy('store_id');
	    if (isset($options['store_id'])) {
	        $table->having('store_id', $options['store_id']);
	    }
	    if (isset($options['day'])) {
	        $day_time = RC_Time::local_strtotime($options['day']);
	    } else {
	        $day_time = RC_Time::local_strtotime(RC_Time::local_date('Y-m-d',RC_Time::gmtime())) - 86400;
	    }
	    $table->whereBetween('add_time', array($day_time, $day_time + 86399));
	    //group by store_id, order_type = 1, order_type = 2,

	    $rs_order = RC_DB::table('store_bill_detail')->groupBy('store_id')->select("store_id", DB::raw("'".$options['day']."' as day"), DB::raw('COUNT(store_id) as order_count'), DB::raw('SUM(brokerage_amount) as order_amount'),
	        DB::raw('0 as refund_count'), DB::raw('0.00 as refund_amount'), DB::raw('NUll as percent_value'), DB::raw('0.00 as brokerage_amount'))
	    ->whereBetween('add_time', array($day_time, $day_time + 86399))->where('order_type', 1)->get();

	    $rs_refund = RC_DB::table('store_bill_detail')->groupBy('store_id')->select("store_id", DB::raw("'".$options['day']."' as day"),DB::raw('COUNT(store_id) as refund_count'), DB::raw('SUM(brokerage_amount) as refund_amount'))
	    ->whereBetween('add_time', array($day_time, $day_time + 86399))->where('order_type', 2)->get();
// 	    _dump($rs_order);

	    //获取结算店铺列表
// 	    $store_list = RC_DB::table('store_franchisee')->where('status', 1)->lists('store_id');
// 	    _dump($store_list);
	    if ($rs_order) {
	        foreach ($rs_order as $key => &$val) {
	            if ($rs_refund) {
	                foreach ($rs_refund as $key2 => $val2) {
	                    if ($val['store_id'] == $val2['store_id'] && $val['day'] == $val2['day']) {
	                        $val['refund_count'] = $val2['refund_count'];
	                        $val['refund_amount'] = $val2['refund_amount'];
	                        $val['brokerage_amount'] = $val['order_amount'] + $val2['refund_amount'];
	                    }
	                }
	            } else {
	                $val['brokerage_amount'] = $val['order_amount'];
	            }
	            $val['add_time'] = RC_Time::gmtime();
// 	            if (!in_array($val['store_id'], $store_list)) {
// 	                $val['brokerage_amount'] = $val['order_amount'];
// 	            }
	        }

	    }
// 	    foreach ($store_list as $store_id) {
// 	        if
// 	    }
// 	    _dump($rs_refund);
// 	    _dump($rs_order,1);

        return $rs_order;
	}
	/* SELECT
	store_id,
	'2016-05-01' AS DAY,
	count(store_id) AS order_count,
	SUM(brokerage_amount)  AS order_amount
	FROM
	`ecjia_store_bill_detail`
	WHERE
	`add_time` BETWEEN 1476172800
	AND 1476172800 + 86399
	AND order_type = 1
	GROUP BY
	`store_id`;

	SELECT
	store_id,
	'2016-05-01' AS DAY,
	count(store_id) AS refund_count,
	SUM(brokerage_amount) AS refund_amount
	FROM
	`ecjia_store_bill_detail`
	WHERE
	`add_time` BETWEEN 1476172800
	AND 1476172800 + 86399
	AND order_type = 2
	GROUP BY
	`store_id`; */
	public function get_bill_percent($order_id) {
	    $rs = RC_DB::table('store_bill_detail')->where('order_id', $order_id)->where('order_type', 1)->first();
	    return $rs['percent_value'];
	}

	public function get_bill_record($store_id, $page = 1, $page_size = 15, $filter, $is_admin = 0) {
	    $db_bill_detail = RC_DB::table('store_bill_detail as bd')
	    ->leftJoin('store_franchisee as s', RC_DB::raw('s.store_id'), '=', RC_DB::raw('bd.store_id'));

	    if ($store_id) {
	        $db_bill_detail->whereRaw('bd.store_id ='.$store_id);
	    }

	    if (!empty($filter['order_sn'])) {
	        $db_bill_detail->whereRaw('oi.order_sn ='.$filter['order_sn']);
	    }
	    if (!empty($filter['merchant_keywords'])) {
	        $db_bill_detail->whereRaw("s.merchants_name like'%".$filter['merchant_keywords']."%'");
	    }
	    if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
	        $db_bill_detail->whereRaw("bd.add_time BETWEEN ".$filter['start_date']." AND ".$filter['end_date']);
	    } else {
	        if (!empty($filter['start_date']) && empty($filter['end_date'])) {
	            $db_bill_detail->whereRaw('bd.add_time >= '.$filter['start_date']);
	        }
	        if (empty($filter['start_date']) && !empty($filter['end_date'])) {
	            $db_bill_detail->whereRaw('bd.add_time <='.$filter['end_date']);
	        }
	    }
	    $db_bill_detail->leftJoin('order_info as oi', RC_DB::raw('bd.order_id'), '=', RC_DB::raw('oi.order_id'));
	    $count = $db_bill_detail->count('detail_id');
	    if($is_admin) {
	        $page = new ecjia_page($count, $page_size, 3);
	    } else {
	        $page = new ecjia_merchant_page($count, $page_size, 3);
	    }
	    

	    $fields = " oi.store_id, oi.order_id, oi.order_sn, oi.add_time as order_add_time, oi.order_status, oi.shipping_status, oi.order_amount, oi.money_paid, oi.is_delete,";
	    $fields .= " oi.shipping_time, oi.auto_delivery_time, oi.pay_status,";
	    $fields .= " bd.*,s.merchants_name,";
	    $fields .= " IFNULL(u.user_name, '" . RC_Lang::get('store::store.anonymous'). "') AS buyer ";

	    $row = $db_bill_detail
	    ->leftJoin('users as u', RC_DB::raw('u.user_id'), '=', RC_DB::raw('oi.user_id'))
	    ->select(RC_DB::raw($fields))
	    ->take($page_size)
	    ->orderBy(RC_DB::raw('bd.add_time'), 'desc')
	    ->skip($page->start_id-1)
	    ->get();

// 	    _dump($row,1);
	    if ($row) {
	        foreach ($row as $key => &$val) {
	            $val['order_add_time_formate'] = RC_Time::local_date('Y-m-d H:i', $val['order_add_time']);
	            $val['add_time_formate'] = RC_Time::local_date('Y-m-d H:i', $val['add_time']);
// 	            $val['status']			 = RC_Lang::lang('os/'.$val['order_status']) . ',' . RC_Lang::lang('ps/'.$val['pay_status']) . ',' . RC_Lang::lang('ss/'.$val['shipping_status']);
	        }
	    }
// 	    _dump($row,1);
	    return array('item' => $row, 'filter' => $filter, 'page' => $page->show(2), 'desc' => $page->page_desc());
	}
}

// end
