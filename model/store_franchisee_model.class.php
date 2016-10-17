<?php
/**
 * 账单 日统计
 */
defined('IN_ECJIA') or exit('No permission resources.');
class store_franchisee_model extends Component_Model_Model {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->table_name = 'store_franchisee';
		parent::__construct();
		
	}
	
	public function get_store_commission_percent($store_id) {
	    
	    if (! $store_id) {
	        return false;
	    }
	    
	    $percent_id = RC_DB::table('store_franchisee')->select('percent_id')->where('store_id', $store_id)->first();
        if (! $percent_id['percent_id']) {
            return false;
        }
        $rs = RC_DB::table('store_percent')->select('percent_value')->where('percent_id', $percent_id['percent_id'])->first();
        return $rs['percent_value'];
	}
	
	
}

// end