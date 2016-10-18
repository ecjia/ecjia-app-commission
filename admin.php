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
		RC_Style::enqueue_style('hint_css', RC_Uri::admin_url('statics/lib/hint_css/hint.min.css'), array(), false, false);
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
//         RC_Script::enqueue_script('bill-init', RC_App::apps_url('statics/js/bill.js',__FILE__));
        RC_Script::enqueue_script('bill-pay', RC_App::apps_url('statics/js/bill_pay.js',__FILE__));
		
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
	
	//账单详情
	//模板顶部表格，月账单情况
	//底部详单列表，可翻页，30条一页
	public function detail() {
	    
	    /* 检查权限 */
	    // 		$this->admin_priv('bill_view');
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/admin/init')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单列表'),  RC_Uri::url('commission/admin/init')));
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
	    $bill_info['merchants_name'] = RC_Model::model('commission/store_franchisee_model')->get_merchants_name($bill_info['store_id']);
	    
	    $this->assign('ur_here', $bill_info['bill_month'].'账单详情');
	    $this->assign('bill_info', $bill_info);
	    
	    //明细
	    $filter['start_date'] = RC_Time::local_strtotime($bill_info['bill_month']);
	    $filter['end_date'] = RC_Time::local_strtotime(RC_Time::local_date('Y-m-d', strtotime('+1 month', $filter['start_date']))) - 1;
	    
	    $record_list = $this->db_store_bill_detail->get_bill_record($bill_info['store_id'], $_GET['page'], 30, $filter);
	    $this->assign('lang_os', RC_Lang::get('orders::order.os'));
	    $this->assign('lang_ps', RC_Lang::get('orders::order.ps'));
	    $this->assign('lang_ss', RC_Lang::get('orders::order.ss'));
	    $this->assign('record_list', $record_list);
	    
	    $this->display('bill_detail.dwt');
	}
	
	//打款
	public function pay() {
	    /* 检查权限 */
	    // 		$this->admin_priv('bill_view');
	    $bill_id = empty($_GET['bill_id']) ? null : intval($_GET['bill_id']);
	    if (empty($bill_id)) {
	        $this->showmessage('参数异常', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    $this->assign('action_link', array('href' => RC_Uri::url('commission/admin/detail', 'id='.$bill_id), 'text' => '账单详情'));
	    
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/admin/init')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单列表'), RC_Uri::url('commission/admin/init')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单详情'), RC_Uri::url('commission/admin/detail', 'id='.$bill_id)));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单打款')));
	    $this->assign('ur_here', '账单打款');
	    
	    //账单信息
	    $bill_info = $this->db_store_bill->get_bill($bill_id);
	    if (empty($bill_info)) {
	        $this->showmessage('没有数据', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    $bill_info['merchants_name'] = RC_Model::model('commission/store_franchisee_model')->get_merchants_name($bill_info['store_id']);
	     
	    $this->assign('bill_info', $bill_info);
	    
	    //打款流水
	    $log_list = RC_Model::model('commission/store_bill_paylog_model')->get_bill_paylog_list($bill_info['bill_id'], 1, 100);
	    $this->assign('log_list', $log_list);
	    
	    //打款信息
	    //根据状态和打款流水和判断是否已经全部打款
	    if ($bill_info['pay_status'] == 3 && $log_list['filter']['count_bill_amount'] != $bill_info['bill_amount']) {
	        $this->showmessage('打款流水异常，请检查', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    if ($bill_info['pay_status'] != 3 && $log_list['filter']['count_bill_amount'] != $bill_info['bill_amount']) {
	        $this->assign('form_action', RC_Uri::url('commission/admin/pay_do'));
	        $merchants_info = RC_Model::model('commission/store_franchisee_model')->get_merchants_info($bill_info['store_id']);
	        $merchants_info['pay_amount'] = $bill_info['bill_amount'] - $log_list['filter']['count_bill_amount'];
	        $this->assign('merchants_info', $merchants_info);
	    }
	     
	    $this->display('bill_pay.dwt');
	}
	
	//打款操作
	//TODO:部分格式不可修改，浮点数运算需要
	public function pay_do() {
	    
	    $this->admin_priv('commission_pay',ecjia::MSGTYPE_JSON);
	    
	    $bill_id             = !empty($_POST['bill_id']) ? intval($_POST['bill_id']) : 0;
	    $pay_amount          = is_numeric($_POST['pay_amount']) ? (double)$_POST['pay_amount'] : 0;
	    $payee               = !empty($_POST['payee']) ? trim($_POST['payee']) : null;
	    $bank_account_number = !empty($_POST['bank_account_number']) ? trim($_POST['bank_account_number']) : null;
	    $bank_name           = !empty($_POST['bank_name']) ? trim($_POST['bank_name']) 	: null;
	    $bank_branch_name    = !empty($_POST['bank_branch_name']) ? trim($_POST['bank_branch_name']) : null;
	    $mobile      	     = !empty($_POST['mobile']) ? trim($_POST['mobile']) : null;
	    
	    if (empty($bill_id) || empty($pay_amount) || empty($payee) || empty($bank_account_number) || empty($bank_name)) {
	        $this->showmessage('请填写完整的数据', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    
	    $bill_info = $this->db_store_bill->get_bill($bill_id);
	    if (empty($bill_info)) {
	        $this->showmessage('非法数据', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    
	    //打款流水
	    $log_list = RC_Model::model('commission/store_bill_paylog_model')->get_bill_paylog_list($bill_info['bill_id'], 1, 100);
	    
	    
	    $bill_unpayed = $bill_info['bill_amount']-$log_list['filter']['count_bill_amount'];
	    $bill_unpayed = (double)$bill_unpayed;

	    if ($pay_amount　< 0) {
	        $this->showmessage('打款金额不正确', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    if ($pay_amount-$bill_unpayed < 0) {
	        $this->showmessage('打款金额超出未付金额', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    if ($bill_info['pay_status'] == 3 || $bill_unpayed == 0) {
	        $this->showmessage('账单已付清', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    $add_time = RC_Time::gmtime();
	    $data = array(
	        'bill_id' => $bill_id,
            'bill_amount' => $pay_amount,
            'payee' => $payee,
            'bank_account_number' => $bank_account_number,
            'bank_name' => $bank_name,
            'bank_branch_name' => $bank_branch_name,
            'mobile' => $mobile,
            'admin_id' => $_SESSION['admin_id'],
            'add_time' => $add_time,
	    );
	    
	    $insert_id = RC_DB::table('store_bill_paylog')->insertGetId($data);
	    if ($insert_id) {
	       //更新主表
	       
	       if ($pay_amount==$bill_unpayed) {
	           $pay_status = 3;
	       } else if ($pay_amount-$bill_unpayed<0) {
	           $pay_status = 2;
	       }
	       
	       RC_DB::table('store_bill')->where('bill_id', $bill_id)->update(array('pay_status' => $pay_status, 'pay_time' => $add_time));
// 	    ecjia_admin::admin_log('商家名是 '.$merchants_name.'，'.'佣金比例是 '.$percent.'%', 'edit', 'store_commission');
	    
	       $this->showmessage('打款记录保存成功', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('commission/admin/pay', array('bill_id' => $bill_id))));
	    }
	    
	}
	
	//打款日志
	public function pay_log() {

	    /* 检查权限 */
	    // 		$this->admin_priv('bill_view');
	    $bill_id = empty($_GET['bill_id']) ? null : intval($_GET['bill_id']);
	    if (empty($bill_id)) {
	        $this->showmessage('参数异常', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    $this->assign('action_link', array('href' => RC_Uri::url('commission/admin/detail', 'id='.$bill_id), 'text' => '账单详情'));
	     
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/admin/init')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单列表'), RC_Uri::url('commission/admin/init')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单详情'), RC_Uri::url('commission/admin/detail', 'id='.$bill_id)));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('打款流水')));
	    $this->assign('ur_here', '打款流水');
	     
	    $this->bill_and_log($bill_id);
	    
	    $this->assign('action', 'pay_log');
	    $this->display('bill_pay.dwt');
	}
	
	private function bill_and_log($bill_id) {
	    //账单信息
	    $bill_info = $this->db_store_bill->get_bill($bill_id);
	    if (empty($bill_info)) {
	        $this->showmessage('没有数据', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    $bill_info['merchants_name'] = RC_Model::model('commission/store_franchisee_model')->get_merchants_name($bill_info['store_id']);
	     
	    $this->assign('bill_info', $bill_info);
	    
	    //打款流水
	    $log_list = RC_Model::model('commission/store_bill_paylog_model')->get_bill_paylog_list($bill_info['bill_id'], 1, 100);
	    $this->assign('log_list', $log_list);
	}
	
	//订单分成列表
	public function commission_list() {
	    
	}
	
	
}

// end