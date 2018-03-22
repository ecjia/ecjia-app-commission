<?php
//
//    ______         ______           __         __         ______
//   /\  ___\       /\  ___\         /\_\       /\_\       /\  __ \
//   \/\  __\       \/\ \____        \/\_\      \/\_\      \/\ \_\ \
//    \/\_____\      \/\_____\     /\_\/\_\      \/\_\      \/\_\ \_\
//     \/_____/       \/_____/     \/__\/_/       \/_/       \/_/ /_/
//
//   上海商创网络科技有限公司
//
//  ---------------------------------------------------------------------------------
//
//   一、协议的许可和权利
//
//    1. 您可以在完全遵守本协议的基础上，将本软件应用于商业用途；
//    2. 您可以在协议规定的约束和限制范围内修改本产品源代码或界面风格以适应您的要求；
//    3. 您拥有使用本产品中的全部内容资料、商品信息及其他信息的所有权，并独立承担与其内容相关的
//       法律义务；
//    4. 获得商业授权之后，您可以将本软件应用于商业用途，自授权时刻起，在技术支持期限内拥有通过
//       指定的方式获得指定范围内的技术支持服务；
//
//   二、协议的约束和限制
//
//    1. 未获商业授权之前，禁止将本软件用于商业用途（包括但不限于企业法人经营的产品、经营性产品
//       以及以盈利为目的或实现盈利产品）；
//    2. 未获商业授权之前，禁止在本产品的整体或在任何部分基础上发展任何派生版本、修改版本或第三
//       方版本用于重新开发；
//    3. 如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回并承担相应法律责任；
//
//   三、有限担保和免责声明
//
//    1. 本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的；
//    2. 用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未获得商业授权之前，我们不承
//       诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任；
//    3. 上海商创网络科技有限公司不对使用本产品构建的商城中的内容信息承担责任，但在不侵犯用户隐
//       私信息的前提下，保留以任何方式获取用户信息及商品信息的权利；
//
//   有关本产品最终用户授权协议、商业授权与技术服务的详细内容，均由上海商创网络科技有限公司独家
//   提供。上海商创网络科技有限公司拥有在不事先通知的情况下，修改授权协议的权力，修改后的协议对
//   改变之日起的新授权用户生效。电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和
//   等同的法律效力。您一旦开始修改、安装或使用本产品，即被视为完全理解并接受本协议的各项条款，
//   在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本
//   授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。
//
//  ---------------------------------------------------------------------------------
//
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * ECJIA 结算管理
 */
class merchant extends ecjia_merchant {

	private $db_user;
	private $db_store_bill;
	private $db_store_bill_day;
	private $db_store_bill_detail;
	private $db_store_bill_paylog;
	public function __construct() {
		parent::__construct();

		$this->db_user				= RC_Model::model('user/users_model');
		$this->db_store_bill        = RC_Model::model('commission/store_bill_model');
		$this->db_store_bill_day    = RC_Model::model('commission/store_bill_day_model');
		$this->db_store_bill_detail = RC_Model::model('commission/store_bill_detail_model');
		$this->db_store_bill_paylog = RC_Model::model('commission/store_bill_paylog_model');
		
		/* 加载所全局 js/css */
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Script::enqueue_script('ecjia-region');
		RC_Script::enqueue_script('smoke');
		
		//时间控件
		RC_Script::enqueue_script('bootstrap-datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datepicker.min.js'));
		RC_Style::enqueue_style('datepicker', RC_Uri::admin_url('statics/lib/datepicker/datepicker.css'));
		
        /*自定义js/css*/
        RC_Script::enqueue_script('bill-init', RC_App::apps_url('statics/js/bill.js',__FILE__), array('ecjia-merchant'), false, 1);
        RC_Style::enqueue_style('mh_fund', RC_App::apps_url('statics/css/mh_fund.css',__FILE__));
        RC_Script::enqueue_script('mh_fund', RC_App::apps_url('statics/js/mh_fund.js',__FILE__), array('ecjia-merchant'), false, 1);
        
        ecjia_merchant_screen::get_current_screen()->set_parentage('commission');
	}
	
	/**
	 * 结算账单列表
	 */
	public function init() {
		/* 检查权限 */
		$this->admin_priv('commission_manage');
	    ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/merchant/init')));
		ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('结算账单')));
		
		$this->assign('ur_here', '结算账单');
		$this->assign('search_action', RC_Uri::url('commission/merchant/init'));
		
		/* 时间参数 */
		$filter['start_date'] = empty($_GET['start_date']) ? null : RC_Time::local_date('Y-m', RC_Time::local_strtotime($_GET['start_date']));
		$filter['end_date'] = empty($_GET['end_date']) ? null : RC_Time::local_date('Y-m', RC_Time::local_strtotime($_GET['end_date']));
		
		$bill_list = $this->db_store_bill->get_bill_list_merchant($_SESSION['store_id'], $_GET['page'], 15, $filter);
		
		foreach ($bill_list['item'] as &$val) {
		    if ($val['pay_status'] == 2) {
		        $val['pay_count'] = $this->db_store_bill_paylog->get_paylog_count($val['bill_id']);
		    }
		}
		$this->assign('bill_list', $bill_list);
		
		$this->display('bill_list.dwt');
	}
	
	
	public function day() {
	    /* 检查权限 */
	    $this->admin_priv('commission_day');
	    ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/merchant/init')));
	    ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('每日账单')));
	    
	    $this->assign('ur_here', '每日账单');
	    $this->assign('search_action', RC_Uri::url('commission/merchant/day'));
	    
	    // 		/* 时间参数 */
	    $filter['start_date'] = empty($_GET['start_date']) ? null : RC_Time::local_date('Y-m-d', RC_Time::local_strtotime($_GET['start_date']));
	    $filter['end_date']   = empty($_GET['end_date']) ? null : RC_Time::local_date('Y-m-d', RC_Time::local_strtotime($_GET['end_date']));
	    $filter['type']       = $_GET['type'];
	    $filter['keywords'] 		 = empty ($_GET['keywords']) 		  ? '' : trim($_GET['keywords']);
	    $filter['merchant_keywords'] = empty ($_GET['merchant_keywords']) ? '' : trim($_GET['merchant_keywords']);
	    
	    $store_id = $_SESSION['store_id'];
	    
	    if ($filter['start_date'] >  $filter['end_date']) {
	        return $this->showmessage('开始时间不能大于结束时间', ecjia::MSGTYPE_HTML | ecjia::MSGSTAT_ERROR);
	    }
	    
	    $bill_list = $this->db_store_bill_day->get_billday_list($store_id, $_GET['page'], 20, $filter);
	    $this->assign('bill_list', $bill_list);
	    
	    $this->display('bill_list_day.dwt');
	}
	
	public function detail() {
	    /* 检查权限 */
        $this->admin_priv('commission_detail');
	    ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/merchant/init')));
	    ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单详情')));
	    $this->assign('action_link', array('href' => RC_Uri::url('commission/merchant/init'), 'text' => '账单列表'));
	    
	    $bill_id = empty($_GET['id']) ? null : intval($_GET['id']);
	    if (empty($bill_id)) {
	        return $this->showmessage('参数异常', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    
	    $bill_info = $this->db_store_bill->get_bill($bill_id, $_SESSION['store_id']);
	    if (empty($bill_info)) {
	        return $this->showmessage('没有数据', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    $bill_info['pay_count'] = $this->db_store_bill_paylog->get_paylog_count($bill_info['bill_id']);
	    
	    $this->assign('ur_here', $bill_info['bill_month'].'账单详情');
	    $this->assign('bill_info', $bill_info);
	    
	    //每日
	    $bill_list = $this->db_store_bill_day->get_billday_list($bill_info['store_id'], 1, 40, array('start_date' => $bill_info['bill_month'].'-01', 'end_date' => $bill_info['bill_month'].'-31'));
	    $this->assign('bill_list', $bill_list);
	    
	    //明细
	    $filter['start_date'] = RC_Time::local_strtotime($bill_info['bill_month']);
	    $filter['end_date'] = RC_Time::local_strtotime(RC_Time::local_date('Y-m',$filter['start_date'] + 86400*31).'-01')-1;
	    
	    $record_list = $this->db_store_bill_detail->get_bill_record($_SESSION['store_id'], $_GET['page'], 30, $filter);
	    
	    $this->assign('lang_os', RC_Lang::get('orders::order.os'));
	    $this->assign('lang_ps', RC_Lang::get('orders::order.ps'));
	    $this->assign('lang_ss', RC_Lang::get('orders::order.ss'));
	    $this->assign('record_list', $record_list);
	    
	    $this->display('bill_detail.dwt');
	    //模板顶部表格，月账单情况
	    //底部详单列表，可翻页，30条一页
	}
	
	
	//订单分成
	public function record() {
	    /* 检查权限 */
	    $this->admin_priv('commission_order');
	    
	    ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/merchant/init')));
	    ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('订单分成')));
	    
	    $this->assign('ur_here', '订单分成');
	    $this->assign('search_action', RC_Uri::url('commission/merchant/record'));
	    
	    /* 时间参数 */
	    $filter['start_date'] = empty($_GET['start_date']) ? null : RC_Time::local_strtotime($_GET['start_date']);
	    $filter['end_date'] = empty($_GET['end_date']) ? null : RC_Time::local_strtotime($_GET['end_date']) + 86399;
	    
	    $record_list = $this->db_store_bill_detail->get_bill_record($_SESSION['store_id'], $_GET['page'], 15, $filter);
	    $this->assign('lang_os', RC_Lang::get('orders::order.os'));
	    $this->assign('lang_ps', RC_Lang::get('orders::order.ps'));
	    $this->assign('lang_ss', RC_Lang::get('orders::order.ss'));
	    
	    $this->assign('lang_os_quickpay', RC_Lang::get('quickpay::order.os'));
	    $this->assign('lang_ps_quickpay', RC_Lang::get('quickpay::order.ps'));
	    $this->assign('lang_vs_quickpay', RC_Lang::get('quickpay::order.vs'));
	    
	    $this->assign('record_list', $record_list);
	    
	    $this->display('bill_record.dwt');
	}
	
	//结算统计
	public function count() {
	    /* 检查权限 */
	    $this->admin_priv('commission_count');
	    
	    ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/merchant/init')));
	    ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('结算统计')));
	     
	    $this->assign('ur_here', '结算统计');
	    $this->assign('search_action', RC_Uri::url('commission/merchant/count'));
	     
	    /* 时间参数 */
	    $filter['start_date'] = empty($_GET['start_date']) ? null : $_GET['start_date'];
	    $filter['end_date'] = empty($_GET['end_date']) ? null : $_GET['end_date'];
	    
	    $bill_day_list = $this->db_store_bill_day->get_billday_list($_SESSION['store_id'], $_GET['page'], 31, $filter);
	    $this->assign('bill_day_list', $bill_day_list);
	     
	    $this->display('bill_count.dwt');
	}
	
	//资金管理
	public function fund() {
		/* 检查权限 */
		$this->admin_priv('fund_manage');
		
		ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/merchant/init')));
		ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('资金管理')));
		
		$this->assign('title', '资金明细');
		$this->assign('ur_here', '资金管理');
		$this->assign('action_link', array('href' => RC_Uri::url('commission/merchant/fund_record'), 'text' => '提现记录'));
		
		$account = $this->get_store_account();
		$this->assign('account', $account);
		
		$data = $this->get_account_order();
		$this->assign('data', $data);
		
		$this->display('fund_list.dwt');
	}
	
	//申请提现
	public function reply_fund() {
		$this->admin_priv('fund_update');
		
		ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/merchant/init')));
		ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('资金管理'), RC_Uri::url('commission/merchant/fund')));
		ecjia_merchant_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('申请提现')));
		
		$this->assign('ur_here', '申请提现');
		$this->assign('action_link', array('href' => RC_Uri::url('commission/merchant/fund'), 'text' => '资金管理'));
		
		$data = $this->get_store_account();
		$this->assign('data', $data);
		
		$bank_info = RC_DB::table('store_franchisee')
			->where('store_id', $_SESSION['store_id'])
			->select('bank_name', 'bank_branch_name', 'bank_account_name', 'bank_account_number','bank_address')
			->first();
		
		if (!empty($bank_info['bank_account_number'])) {
			$bank_account_number = $this->substr_cut($bank_info['bank_account_number']);
			$bank_info['bank_account_number'] = ' ( '.$bank_account_number.' ) ';
		}
		$this->assign('bank_info', $bank_info);
		$this->assign('form_action', RC_Uri::url('commission/merchant/add_reply'));
		
		$this->display('reply_fund.dwt');
	}
	
	//添加申请
	public function add_reply() {
		$this->admin_priv('fund_update', ecjia::MSGTYPE_JSON);
		
		return $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}
	
	//提现记录
	public function fund_record() {
		
	}
	
	//获取店铺账户信息
	private function get_store_account() {
		$data = RC_DB::table('store_account')->where('store_id', $_SESSION['store_id'])->first();
		if (empty($data)) {
			$data['formated_amount_available'] = $data['formated_money'] = $data['formated_frozen_money'] = $data['formated_deposit'] = '￥0.00';
			$data['amount_available'] = $data['money'] = $data['frozen_money'] = $data['deposit'] = '0.00';
		} else {
			$amount_available = $data['money'] - $data['deposit'];//可用余额=money-保证金
			$data['formated_amount_available'] = price_format($amount_available);
			$data['amount_available'] = $amount_available;
			
			$money = $data['money'] + $data['frozen_money'];//总金额=money+冻结
			$data['formated_money'] = price_format($money);
			$data['money'] = $money;
			
			$data['formated_frozen_money'] = price_format($data['frozen_money']);
			$data['formated_deposit'] = price_format($data['deposit']);
		}
		return $data;
	}
	
	//获取资金明细
	private function get_account_order() {
		$db = RC_DB::table('store_account_order');
		
		$db->where('store_id', $_SESSION['store_id']);
		$count = $db->count();
		$page = new ecjia_page($count, 10, 5);
		$data = $db->select('order_sn', 'add_time', 'process_type', 'bill_order_type', 'amount')->take(10)->skip($page->start_id - 1)->orderBy('add_time', 'desc')->get();
		
		return array('item' => $data, 'page' => $page->show(2), 'desc' => $page->page_desc());
	}
	
	//截取字符串
	private function substr_cut($str = ''){
		//获取字符串长度
		$strlen = mb_strlen($str);
		//如果字符串长度小于2，不做任何处理
		if ($strlen < 2) {
			return $str;
		} else {
			//mb_substr — 获取字符串的部分
			$firstStr = mb_substr($str, 0, 4);
			$lastStr = mb_substr($str, -4, 4);
			//str_repeat — 重复一个字符串
			return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($str) - 1) : $firstStr . str_repeat("*", $strlen - 8) . $lastStr;
		}
	}
}

// end