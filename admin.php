<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * ECJIA 结算管理
 */

class admin extends ecjia_admin {

	private $db_user;
	private $db_store_bill;
	private $db_store_bill_day;
	private $db_store_bill_detail;
	public function __construct() {
		parent::__construct();

		$this->db_user				= RC_Model::model('user/users_model');
		$this->db_store_bill        = RC_Model::model('commission/store_bill_model');
		$this->db_store_bill_day    = RC_Model::model('commission/store_bill_day_model');
		$this->db_store_bill_detail = RC_Model::model('commission/store_bill_detail_model');
		
		/* 加载所全局 js/css */
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Script::enqueue_script('ecjia-region');
		
		/* 列表页 js/css */
		RC_Script::enqueue_script('smoke');
// 		RC_Script::enqueue_script('jquery-chosen');
// 		RC_Style::enqueue_style('chosen');
		
		/* 编辑页 js/css */	
// 		RC_Style::enqueue_style('uniform-aristo');
// 		RC_Script::enqueue_script('jquery-uniform');
		RC_Style::enqueue_style('datepicker', RC_Uri::admin_url('statics/lib/datepicker/datepicker.css'), array(), false, false);
		RC_Script::enqueue_script('bootstrap-datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datepicker.min.js'));
// 		RC_Style::enqueue_style('bootstrap-editable', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css'), array(), false, false);
// 		RC_Script::enqueue_script('bootstrap-editable.min', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js'));

		//时间控件
		RC_Style::enqueue_style('datepicker', RC_Uri::admin_url('statics/lib/datepicker/datepicker.css'), array('ecjia-merchant'), false, 1);
        RC_Script::enqueue_script('bootstrap-datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datepicker.min.js'), array('ecjia-merchant'), false, 1);
        
        /*自定义js*/
        RC_Script::enqueue_script('bill-init', RC_App::apps_url('statics/js/bill.js',__FILE__), array('ecjia-merchant'), false, 1);
		
	}
	
	/**
	 * 结算账单列表
	 */
	public function init() {
		/* 检查权限 */
// 		$this->admin_priv('bill_view');
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/admin/init')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单列表')));
		
		$this->assign('ur_here', '账单列表');
		$this->assign('search_action', RC_Uri::url('commission/admin/init'));
		
// 		/* 时间参数 */
		$filter['start_date'] = empty($_GET['start_date']) ? null : RC_Time::local_date('Y-m', RC_Time::local_strtotime($_GET['start_date']));
		$filter['end_date'] = empty($_GET['end_date']) ? null : RC_Time::local_date('Y-m', RC_Time::local_strtotime($_GET['end_date']));
		$filter['type'] = $_GET['type'];
		
		$bill_list = $this->db_store_bill->get_bill_list(null, $_GET['page'], 15, $filter);
		$this->assign('bill_list', $bill_list);
		
		$this->display('bill_list.dwt');
	}
	
	public function detail() {
	    
	    /* 检查权限 */
	    // 		$this->admin_priv('bill_view');
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/admin/init')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单详情')));
	    $this->assign('action_link', array('href' => RC_Uri::url('commission/admin/init'), 'text' => '账单列表'));
	    
	    $bill_id = empty($_GET['id']) ? null : intval($_GET['id']);
	    if (empty($bill_id)) {
	        $this->showmessage('参数异常', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    
	    $bill_info = $this->db_store_bill->get_bill($bill_id);
	    if (empty($bill_info)) {
	        $this->showmessage('没有数据', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    
	    $this->assign('ur_here', $bill_info['bill_month'].'账单详情');
	    $this->assign('bill_info', $bill_info);
	    
	    //明细
	    $filter['start_date'] = RC_Time::local_strtotime($bill_info['bill_month']);
	    $filter['end_date'] = RC_Time::local_strtotime(RC_Time::local_date('Y-m-d', strtotime('+1 month', $filter['start_date']))) - 1;
	    
	    $record_list = $this->db_store_bill_detail->get_bill_record($bill_info['store_id'], $_GET['page'], 1, $filter);
	    $this->assign('lang_os', RC_Lang::get('orders::order.os'));
	    $this->assign('lang_ps', RC_Lang::get('orders::order.ps'));
	    $this->assign('lang_ss', RC_Lang::get('orders::order.ss'));
	    $this->assign('record_list', $record_list);
	    
	    $this->display('bill_detail.dwt');
	    //模板顶部表格，月账单情况
	    //底部详单列表，可翻页，30条一页
	}
	
	
}

// end