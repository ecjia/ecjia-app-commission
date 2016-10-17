<?php
/**
 * 账单 日统计
 */
defined('IN_ECJIA') or exit('No permission resources.');
class store_bill_day_model extends Component_Model_Model {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->table_name = 'store_bill_day';
		parent::__construct();
		
	}
	//TODO::大数据处理
	public function add_bill_day($options) {
	    //已有账单数据
	    $data = RC_Model::model('commission/store_bill_detail_model')->count_bill_day($options);
	    
//         if (!is_array($data) || !isset($data['store_id']) || !isset($data['day']) || !isset($data['order_count'])
//             || !isset($data['order_amount']) || !isset($data['percent_value']) || !isset($data['brokerage_amount']) || !isset($data['add_time'])) {
//             return false;
//         }

	    //获取结算店铺列表
// 	    $store_list = RC_DB::table('store_franchisee')->select('store_id')->where('status', 1)->get();
        if (! $data) {
            return false;
        }
//         _dump($data,1);
	    return RC_DB::table('store_bill_day')->insert($data);
	}
	
	public function get_billday_list($store_id, $page = 1, $page_size = 15, $filter) {
	    $db_bill_day = RC_DB::table('store_bill_day');
	    
	    if ($store_id) {
	        $db_bill_day->whereRaw('store_id ='.$store_id);
	    }
	    
	    if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
	        $db_bill_day->whereRaw("day BETWEEN '".$filter['start_date']."' AND '".$filter['end_date']."'");
	    } else {
	        if (!empty($filter['start_date']) && empty($filter['end_date'])) {
	            $db_bill_day->whereRaw("day >= '".$filter['start_date']."'");
	        }
	        if (empty($filter['start_date']) && !empty($filter['end_date'])) {
	            $db_bill_day->whereRaw("day <= '".$filter['end_date']."'");
	        }
	    }
	    $count = $db_bill_day->count();
	    $page = new ecjia_page($count, $page_size, 6);
	     
	    $row = $db_bill_day
	    ->take($page_size)
	    ->orderBy('day', 'desc')
	    ->skip($page->start_id-1)
	    ->get();
	     
	    if ($row) {
	        foreach ($row as $key => &$val) {
	            $val['add_time_formate'] = RC_Time::local_date('Y-m-d H:i', $val['add_time']);
	        }
	    }
	    // 	    _dump($row,1);
	    return array('item' => $row, 'filter' => $filter, 'page' => $page->show(3), 'desc' => $page->page_desc());
	    
	}
	
	
}

// end