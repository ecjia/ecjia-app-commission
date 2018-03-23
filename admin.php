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
		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('jquery-chosen');
		RC_Style::enqueue_style('chosen');
		RC_Script::enqueue_script('jquery-uniform');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Style::enqueue_style('hint_css', RC_Uri::admin_url('statics/lib/hint_css/hint.min.css'), array(), false, false);
		
		//时间控件
		RC_Script::enqueue_script('bootstrap-datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datepicker.min.js'));
		RC_Style::enqueue_style('datepicker', RC_Uri::admin_url('statics/lib/datepicker/datepicker.css'));
        
        /*自定义js*/
        RC_Script::enqueue_script('bill-admin', RC_App::apps_url('statics/js/bill_admin.js', __FILE__));
        RC_Script::enqueue_script('bill-pay', RC_App::apps_url('statics/js/bill_pay.js', __FILE__));
        RC_Script::enqueue_script('bill-order', RC_App::apps_url('statics/js/order.js', __FILE__));
        RC_Script::enqueue_script('bill-update', RC_App::apps_url('statics/js/bill_update.js', __FILE__));
        
        RC_Script::enqueue_script('withdraw', RC_App::apps_url('statics/js/withdraw.js', __FILE__));
        RC_Style::enqueue_style('mh_fund', RC_App::apps_url('statics/css/mh_fund.css',__FILE__));
	}
	
	/**
	 * 结算账单列表
	 */
	public function init() {
		/* 检查权限 */
		$this->admin_priv('commission_manage');
	    
		$this->assign('search_action', RC_Uri::url('commission/admin/init'));
		$this->assign('ur_here', '账单列表');
		$this->assign('action_link',	array('text' => '账单生成', 'href' => RC_Uri::url('commission/admin/bill_update')));
		
 		/* 时间参数 */
		$filter['start_date'] = empty($_GET['start_date']) ? null : RC_Time::local_date('Y-m', RC_Time::local_strtotime($_GET['start_date']));
		$filter['end_date']   = empty($_GET['end_date']) ? null : RC_Time::local_date('Y-m', RC_Time::local_strtotime($_GET['end_date']));
		$filter['type']       = $_GET['type'];
		$filter['keywords'] 		 = empty ($_GET['keywords']) 		  ? '' : trim($_GET['keywords']);
		$filter['merchant_keywords'] = empty ($_GET['merchant_keywords']) ? '' : trim($_GET['merchant_keywords']);
		
		$store_id = empty($_GET['store_id']) ? null :$_GET['store_id'];
		
		if ($_GET['refer'] == 'store') {
		    RC_loader::load_app_func('global', 'store');
		   
		    $store = RC_DB::table('store_franchisee')->where('store_id', $store_id)->first();
		    if ($store['manage_mode'] == 'self') {
		    	ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('自营店铺'), RC_Uri::url('store/admin/init')));
		    } else {
		    	ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('入驻商家'), RC_Uri::url('store/admin/join')));
		    }
		    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here($store['merchants_name'], RC_Uri::url('store/admin/preview', array('store_id' => $store_id))));
		    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here('账单列表'));
		    
		    ecjia_screen::get_current_screen()->set_sidebar_display(false);
		    ecjia_screen::get_current_screen()->add_option('store_name', $store['merchants_name']);
		    ecjia_screen::get_current_screen()->add_option('current_code', 'store_admin_commission');
		} else {
			ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here('账单列表'));
		}
		
		if ($store_id) {
		    $merchants_name = RC_DB::table('store_franchisee')->where('store_id', $store_id)->pluck('merchants_name');
		    $this->assign('merchants_name', $merchants_name);
		    $this->assign('ur_here', $merchants_name.' - 账单列表');
		}
		
		$url_parames = '';
		if(!empty($_GET['store_id'])) {
		    $url_parames .= '&store_id='.intval($_GET['store_id']);
		}
		if(!empty($_GET['keywords'])) {
		    $url_parames .= '&keywords='.$_GET['keywords'];
		}
		if(!empty($_GET['merchant_keywords'])) {
		    $url_parames .= '&merchant_keywords='.$_GET['merchant_keywords'];
		}
		if(!empty($_GET['start_date'])) {
		    $url_parames .= '&start_date='.$_GET['start_date'];
		}
		if(!empty($_GET['end_date'])) {
		    $url_parames .= '&end_date='.$_GET['end_date'];
		}
		$this->assign('url_parames', $url_parames);
		
		$bill_list = $this->db_store_bill->get_bill_list($store_id, $_GET['page'], 20, $filter);
		$this->assign('bill_list', $bill_list);
		
		$this->display('bill_list.dwt');
	}
	
	public function bill_update() {
	    /* 检查权限 */
	    $this->admin_priv('commission_update');
	    $this->assign('ur_here', '月账单生成');
	    $this->assign('action_link', array('text' => '每月账单', 'href' => RC_Uri::url('commission/admin/init')));
	     
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('每月账单'), RC_Uri::url('commission/admin/init')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单生成')));
	     
	    $this->assign('form_action', RC_Uri::url('commission/admin/bill_updata_month'));
	     
	    $this->display('bill_list_month_update.dwt');
	}
	
	public function bill_updata_month() {
	    /* 检查权限 */
	    $this->admin_priv('commission_update');
	     
	    set_time_limit(300);
	     
	    $start_date = $_POST['start_date'];
	    if (empty($start_date)) {
	        return $this->showmessage('请选择时间', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    
	    if ($start_date == RC_Time::local_date('Y-m')) {
	        return $this->showmessage('当月交易未完成，账单暂不能生成', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	     
	    RC_Loader::load_app_class('store_bill', 'commission', false);
	    $store_bill = new store_bill();
	     
	    $store_bill->bill_month(array('month' => $start_date));
	     
	    return $this->showmessage('更新成功', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	     
	}
	
	public function bill_refresh() {
	    /* 检查权限 */
	    $this->admin_priv('commission_refresh');
	    $id = $_POST['id'];
	    if(empty($id)) {
	        return $this->showmessage('参数错误', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    $bill_info = RC_DB::table('store_bill')->where('bill_id', $id)->first();
	    if (empty($bill_info)) {
	        return $this->showmessage('账单信息不存在', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    if ($bill_info['bill_month'] == RC_Time::local_date('Y-m')) {
	        return $this->showmessage('当月交易未完成，账单暂不能生成', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    
	    //重新生成
	    set_time_limit(300);
	    RC_Loader::load_app_class('store_bill', 'commission', false);
	    $store_bill = new store_bill();
	    
	    $store_bill->bill_month_refresh(array('month' => $bill_info['bill_month'], 'store_id' => $bill_info['store_id']));
	    
	    return $this->showmessage('账单生成成功', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('commission/admin/detail', array('id' => $id))));
	    
	}
	
	public function day() {
	    /* 检查权限 */
	    $this->admin_priv('commission_day_manage');
	     
	    $this->assign('search_action', RC_Uri::url('commission/admin/day'));
	    $this->assign('ur_here', '每日账单');
	    $this->assign('action_link', array('text' => '账单生成', 'href' => RC_Uri::url('commission/admin/day_update')));
	
	    /* 时间参数 */
	    $filter['start_date'] = empty($_GET['start_date']) ? null : RC_Time::local_date('Y-m-d', RC_Time::local_strtotime($_GET['start_date']));
	    $filter['end_date']   = empty($_GET['end_date']) ? null : RC_Time::local_date('Y-m-d', RC_Time::local_strtotime($_GET['end_date']));
	    $filter['type']       = $_GET['type'];
	    $filter['keywords'] 		 = empty ($_GET['keywords']) 		  ? '' : trim($_GET['keywords']);
	    $filter['merchant_keywords'] = empty ($_GET['merchant_keywords']) ? '' : trim($_GET['merchant_keywords']);
	
	    $store_id = empty($_GET['store_id']) ? null :$_GET['store_id'];
	
	    if ($_GET['refer'] == 'store') {
	        RC_loader::load_app_func('global', 'store');
	        ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('入驻商'), RC_Uri::url('store/admin/init')));
	        
	        $store = RC_DB::table('store_franchisee')->where('store_id', $store_id)->first();
	        ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here($store['merchants_name'], RC_Uri::url('store/admin/preview', array('store_id' => $store_id))));
	        ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here('每日账单'));
	        
	        ecjia_screen::get_current_screen()->set_sidebar_display(false);
	        ecjia_screen::get_current_screen()->add_option('store_name', $store['merchants_name']);
	        ecjia_screen::get_current_screen()->add_option('current_code', 'store_admin_commission');
	    } else {
	    	ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('每日账单')));
	    }
	    
	    if ($store_id) {
	        $merchants_name = RC_DB::table('store_franchisee')->where('store_id', $store_id)->pluck('merchants_name');
	        $this->assign('merchants_name', $merchants_name);
	        $this->assign('ur_here', $merchants_name.' - 每日账单');
	    }
	    if ($filter['start_date'] >  $filter['end_date']) {
	        return $this->showmessage('开始时间不能大于结束时间', ecjia::MSGTYPE_HTML | ecjia::MSGSTAT_ERROR);
	    }
	
	    $bill_list = $this->db_store_bill_day->get_billday_list($store_id, $_GET['page'], 20, $filter);
	    $this->assign('bill_list', $bill_list);
	
	    $this->display('bill_list_day.dwt');
	}
	
	public function day_update() {
	    /* 检查权限 */
	    $this->admin_priv('commission_day_update');
	    $this->assign('ur_here', '每日账单生成');
	    $this->assign('action_link',	array('text' => '每日账单', 'href' => RC_Uri::url('commission/admin/day')));
	    
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('每日账单'), RC_Uri::url('commission/admin/day')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单生成')));
	    
	    $this->assign('form_action', RC_Uri::url('commission/admin/bill_updata_day'));
	    
	    $this->display('bill_list_day_update.dwt');
	}
	
	public function bill_updata_day() {
	    /* 检查权限 */
	    $this->admin_priv('commission_day_update');
	    
	    set_time_limit(300);
	    
	    $start_date = $_POST['start_date'];
	    $end_date = $_POST['end_date'];
	    if (empty($start_date) || empty($end_date)) {
	        return $this->showmessage('请选择时间', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    $seconds_end = RC_Time::local_strtotime($_POST['end_date']);
	    $seconds_start = RC_Time::local_strtotime($_POST['start_date']);
	    if ($_POST['end_date'] > RC_Time::local_date('Y-m-d', RC_Time::gmtime())) {
	        $seconds_end = RC_Time::local_strtotime(RC_Time::local_date('Y-m-d', RC_Time::gmtime())) - 86400;
	    }
	    if ($seconds_end < $seconds_start) {
	        return $this->showmessage('开始时间不能大于结束时间', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    $days = ($seconds_end-$seconds_start)/86400;
	    
	    RC_Loader::load_app_class('store_bill', 'commission', false);
	    $store_bill = new store_bill();
	    
	    for ($i=0; $i<$days+1; $i++) {
	        $date = RC_Time::local_date('Y-m-d', $seconds_start + $i * 86400);
	        $store_bill->bill_day(array('day' => $date));
	    }
	    return $this->showmessage('更新成功', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}
	
	//账单详情
	//模板顶部表格，月账单情况
	//底部详单列表，可翻页，30条一页
	public function detail() {
	    /* 检查权限 */
	    $this->admin_priv('commission_detail');
	    
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/admin/init')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单列表'),  RC_Uri::url('commission/admin/init')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('账单详情')));
	    if ($_GET['store_id']) {
	        $action_link_href = RC_Uri::url('commission/admin/init', array('store_id' => $_GET['store_id']));
	    } else {
	        $action_link_href = RC_Uri::url('commission/admin/init');
	    }
	    $this->assign('action_link', array('href' => $action_link_href, 'text' => '账单列表'));
	    
	    $bill_id = empty($_GET['id']) ? null : intval($_GET['id']);
	    if (empty($bill_id)) {
	        return $this->showmessage('参数异常', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    
	    $bill_info = $this->db_store_bill->get_bill($bill_id);
	    if (empty($bill_info)) {
	        return $this->showmessage('没有数据', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    $bill_info['pay_count'] = RC_Model::model('commission/store_bill_paylog_model')->get_paylog_count($bill_info['bill_id']);
	    $bill_info['merchants_name'] = RC_Model::model('commission/store_franchisee_model')->get_merchants_name($bill_info['store_id']);
	    
	    $this->assign('ur_here', $bill_info['bill_month'].'账单详情');
	    $this->assign('bill_info', $bill_info);
	    
	    //每日
	    $bill_list = $this->db_store_bill_day->get_billday_list($bill_info['store_id'], 1, 40, array('start_date' => $bill_info['bill_month'].'-01', 'end_date' => $bill_info['bill_month'].'-31'));
	    $this->assign('bill_list', $bill_list);
	    
	    //明细
	    $filter['start_date'] = RC_Time::local_strtotime($bill_info['bill_month']);
	    $filter['end_date'] = RC_Time::local_strtotime(RC_Time::local_date('Y-m',$filter['start_date'] + 86400*31).'-01')-1;
	    
	    $record_list = $this->db_store_bill_detail->get_bill_record($bill_info['store_id'], $_GET['page'], 30, $filter, 1);
	    $this->assign('lang_os', RC_Lang::get('orders::order.os'));
	    $this->assign('lang_ps', RC_Lang::get('orders::order.ps'));
	    $this->assign('lang_ss', RC_Lang::get('orders::order.ss'));
	    
   		$this->assign('lang_os_quickpay', RC_Lang::get('quickpay::order.os'));
	    $this->assign('lang_ps_quickpay', RC_Lang::get('quickpay::order.ps'));
	    $this->assign('lang_vs_quickpay', RC_Lang::get('quickpay::order.vs'));
	    
	    $this->assign('record_list', $record_list);
	    $this->display('bill_detail.dwt');
	}
	
	
	public function export() {
	    /**
	     * 账单编号
	     * 店铺名
	     * 月份
	     * 佣金百分比
	     * 账单金额
	     * 收款人	
	     * 银行账号	
	     * 收款银行	
	     * 开户行支行
	     * 入账订单数
	     * 入账总金额
	     * 退款订单数
	     * 退款总金额
	     */
	    $filter['start_date'] = empty($_GET['start_date']) ? null : RC_Time::local_date('Y-m', RC_Time::local_strtotime($_GET['start_date']));
	    $filter['end_date']   = empty($_GET['end_date']) ? null : RC_Time::local_date('Y-m', RC_Time::local_strtotime($_GET['end_date']));
	    $filter['type']       = $_GET['type'];
	    $filter['keywords'] 		 = empty ($_GET['keywords']) 		  ? '' : trim($_GET['keywords']);
	    $filter['merchant_keywords'] = empty ($_GET['merchant_keywords']) ? '' : trim($_GET['merchant_keywords']);
	    
        $bill_list = $this->db_store_bill->get_bill_list(0, 0, 0, $filter);
	    
        /* RC_Excel::create('结算账单'.RC_Time::local_date('Ymd'), function($excel) use ($bill_list){
            $excel->sheet('First sheet', function($sheet) use ($bill_list) {
                $sheet->setAutoSize(true);
                $sheet->setWidth('B', 20);
                $sheet->setWidth('E', 15);
                $sheet->setWidth('G', 26);
                $sheet->setWidth('H', 20);
                $sheet->setWidth('I', 25);
                $sheet->setWidth('K', 15);
                $sheet->setWidth('M', 15);
                $sheet->row(1, array(
                    '账单编号', '店铺名', '月份', '佣金百分比', '账单金额', 
                    '收款人', '银行账号', '收款银行', '开户行支行',
                    '入账订单数', '入账总金额', '退款订单数', '退款总金额'
                ));
                foreach ($bill_list as $item) {
                    $sheet->appendRow($item);
                }
            });
        })->download('xls'); */
        
        RC_Excel::load(RC_APP_PATH . 'commission' . DIRECTORY_SEPARATOR .'statics/bill.xls', function($excel) use ($bill_list){
            $excel->sheet('First sheet', function($sheet) use ($bill_list) {
                foreach ($bill_list as $key => $item) {
                    $sheet->appendRow($key+2, $item);
                }
            });
        })->download('xls');
	}
	
	//订单分成列表
	public function order() {
	    /* 检查权限 */
	    $this->admin_priv('commission_order');
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家结算'), RC_Uri::url('commission/admin/init')));
	    ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('订单分成')));
	    $this->assign('ur_here', '订单分成列表');
	    $this->assign('search_action', RC_Uri::url('commission/admin/order'));
	    
	    //明细
// 	    $filter['start_date'] = RC_Time::local_strtotime($bill_info['bill_month']);
// 	    $filter['end_date'] = RC_Time::local_strtotime(RC_Time::local_date('Y-m-d', strtotime('+1 month', $filter['start_date']))) - 1;
	    $filter['order_sn'] = !empty($_GET['order_sn']) ? trim($_GET['order_sn']) : null;
	    $filter['merchant_keywords'] = !empty($_GET['merchant_keywords']) ? trim($_GET['merchant_keywords']) : null;
	    $store_id = !empty($_GET['store_id']) ? intval($_GET['store_id']) : null;
	    
	    if ($store_id) {
	        $merchants_name = RC_DB::table('store_franchisee')->where('store_id', $store_id)->pluck('merchants_name');
	        $this->assign('ur_here', $merchants_name.' - 订单分成列表');
	    }
	     
	    $record_list = $this->db_store_bill_detail->get_bill_record($store_id, $_GET['page'], 20, $filter, 1);
	    $this->assign('lang_os', RC_Lang::get('orders::order.os'));
	    $this->assign('lang_ps', RC_Lang::get('orders::order.ps'));
	    $this->assign('lang_ss', RC_Lang::get('orders::order.ss'));
	    $this->assign('record_list', $record_list);
	    $this->display('order_list.dwt');
	}
	
	public function withdraw() {
		/* 检查权限 */
		$this->admin_priv('commission_withdraw');
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here('商家提现'));
		
		$this->assign('ur_here', '商家提现');
		$this->assign('search_action', RC_Uri::url('commission/admin/withdraw'));
		
		$data = $this->get_account_order();

		$this->assign('data', $data);
		$this->assign('type_count', $data['count']);
		$this->assign('filter', $data['filter']);
		
		$url_parames = '';
		if (!empty($_GET['keywords'])) {
			$url_parames .= '&keywords='.$_GET['keywords'];
		}
		if (!empty($_GET['merchant_keywords'])) {
			$url_parames .= '&merchant_keywords='.$_GET['merchant_keywords'];
		}
		if (!empty($_GET['start_time'])) {
			$url_parames .= '&start_time='.$_GET['start_time'];
		}
		if (!empty($_GET['end_time'])) {
			$url_parames .= '&end_time='.$_GET['end_time'];
		}
		$this->assign('url_parames', $url_parames);
		
		$this->display('withdraw_list.dwt');
	}
	
	public function withdraw_detail() {
		/* 检查权限 */
		$this->admin_priv('commission_withdraw');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('商家提现'), RC_Uri::url('commission/admin/withdraw')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('提现详情')));
		
		$this->assign('ur_here', '提现详情');
		$this->assign('action_link', array('href' => RC_Uri::url('commission/admin/withdraw'), 'text' => '商家提现'));
		$this->assign('form_action', RC_Uri::url('commission/admin/withdraw_update'));
		
		$id = intval($_GET['id']);
		$data = RC_DB::table('store_account_order')->where('id', $id)->first();
		if (!empty($data)) {
			$data['format_amount'] = price_format($data['amount']);
			$data['add_time'] = RC_Time::local_date('Y-m-d H:i:s', $data['add_time']);
			$data['audit_time'] = RC_Time::local_date('Y-m-d H:i:s', $data['audit_time']);
		}
		$this->assign('data', $data);
		$this->assign('status', $data['status']);
		
		$this->display('withdraw_detail.dwt');
	}
	
	public function withdraw_update() {
		$id = intval($_POST['id']);
		$admin_note = trim($_POST['admin_note']);
		if (empty($admin_note)) {
			return $this->showmessage('备注信息不能为空', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$data['admin_id'] = $_SESSION['admin_id'];
		$data['admin_name'] = $_SESSION['admin_name'];
		$data['admin_note'] = $admin_note;
		$data['audit_time'] = RC_Time::gmtime();
		if (isset($_POST['agree'])) {
			//同意
			$data['status'] = 2;	
		} elseif (isset($_POST['refuse'])) {
			$data['status'] = 3;
		}
		$update = RC_DB::table('store_account_order')->where('id', $id)->update($data);
		if ($update) {
			$info = RC_DB::table('store_account_order')->where('id', $id)->first();
			if ($data['status'] == 2) {
				RC_DB::table('store_account')->where('store_id', $info['store_id'])->decrement('frozen_money', $info['amount']);
				$store_account = RC_DB::table('store_account')->where('store_id', $info['store_id'])->first();
				$log = array(
					'store_id' 		=> $info['store_id'],
					'store_money' 	=> $store_account['money'],
					'money'			=> '-'.$info['amount'],
					'frozen_money'  => $store_account['frozen_money'],
					'points'		=> $store_account['points'],
					'change_time'   => RC_Time::gmtime(),
					'change_desc'   => $info['order_sn'],
					'change_type'   => 'withdraw'
 				);
				RC_DB::table('store_account_log')->insert($log);
			} elseif ($data['status'] == 3) {
				$money = RC_DB::table('store_account')->where('store_id', $info['store_id'])->pluck('money');
				RC_DB::table('store_account')->where('store_id', $info['store_id'])->update(array('money_before' => $money));
				
				RC_DB::table('store_account')->where('store_id', $info['store_id'])->increment('money', $info['amount']);
				RC_DB::table('store_account')->where('store_id', $info['store_id'])->decrement('frozen_money', $info['amount']);
			}
		}
		return $this->showmessage('操作成功', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('commission/admin/withdraw_detail', array('id' => $id))));
	}
	
	public function withdraw_export() {
		$filter['start_time'] = empty($_GET['start_time']) ? '' : RC_Time::local_date('Y-m-d', RC_Time::local_strtotime($_GET['start_time']));
		$filter['end_time']   = empty($_GET['end_time']) ? '' : RC_Time::local_date('Y-m-d', RC_Time::local_strtotime($_GET['end_time']));
		$filter['keywords'] 		 = empty ($_GET['keywords']) 		  ? '' : trim($_GET['keywords']);
		$filter['merchant_keywords'] = empty ($_GET['merchant_keywords']) ? '' : trim($_GET['merchant_keywords']);
		 
		$db = RC_DB::table('store_account_order as s')->leftJoin('store_franchisee as sf', RC_DB::raw('s.store_id'), '=', RC_DB::raw('sf.store_id'));
		
		if (!empty($filter['keywords'])) {
			$db->where(RC_DB::raw('s.order_sn'), 'like', '%'.mysql_like_quote($filter['keywords']).'%');
		}
		if (!empty($filter['start_time'])) {
			$db->where(RC_DB::raw('s.add_time'), '>=', RC_Time::local_strtotime($filter['start_time']));
		}
		if (!empty($filter['end_time'])) {
			$db->where(RC_DB::raw('s.add_time'), '<', RC_Time::local_strtotime($filter['end_time']));
		}
		if (!empty($filter['merchant_keywords'])) {
			$db->where(RC_DB::raw('sf.merchants_name'), '<', RC_Time::local_strtotime($filter['merchant_keywords']));
		}
		
		$type = trim($_GET['type']);
		if (empty($type)) {
			$db->where(RC_DB::raw('s.status'), 1);
		}
		if ($type == 1) {
			$db->where(RC_DB::raw('s.status'), 2);
		}
		if ($type == 2) {
			$db->where(RC_DB::raw('s.status'), 3);
		}
		$data = $db->select(RC_DB::raw('s.*'), RC_DB::raw('sf.merchants_name'))->orderBy(RC_DB::raw('s.add_time'), 'desc')->get();

		$arr = [];
		if (!empty($data)) {
			foreach ($data as $k => $v) {
				$arr[$k]['order_sn'] = price_format($v['amount']);
				$arr[$k]['merchants_name'] = $v['merchants_name'];
				$arr[$k]['account_type'] = $v['account_type'] == 'bank' ? '银行卡' : ($v['account_type'] == 'alipay' ? '支付宝' : '');
				$arr[$k]['amount'] = price_format($v['amount']);
				$bank_name = !empty($v['bank_name']) ? '（'.$v['bank_name'].'）' : '';
				$arr[$k]['bank_info'] = $bank_name.$v['account_number'];
				$arr[$k]['add_time'] = RC_Time::local_date('Y-m-d H:i:s', $v['add_time']);
				if ($v['status'] == 1) {
					$status = '待审核';
				}
				if ($v['status'] == 2) {
					$status = '已通过';
				}
				if ($v['status'] == 3) {
					$status = '已拒绝';
				}
				$arr[$k]['status'] = $status;
			}
		}
		RC_Excel::load(RC_APP_PATH . 'commission' . DIRECTORY_SEPARATOR .'statics/withdraw.xls', function($excel) use ($arr){
			$excel->sheet('First sheet', function($sheet) use ($arr) {
				foreach ($arr as $key => $item) {
					$sheet->appendRow($key+2, $item);
				}
			});
		})->download('xls');
	}
	
	private function bill_and_log($bill_id) {
		//账单信息
		$bill_info = $this->db_store_bill->get_bill($bill_id);
		if (empty($bill_info)) {
			return $this->showmessage('没有数据', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$bill_info['merchants_name'] = RC_Model::model('commission/store_franchisee_model')->get_merchants_name($bill_info['store_id']);
		$this->assign('bill_info', $bill_info);
		//打款流水
		$log_list = RC_Model::model('commission/store_bill_paylog_model')->get_bill_paylog_list($bill_info['bill_id'], 1, 100);
		$this->assign('log_list', $log_list);
	}
	
	private function get_account_order() {
		$db = RC_DB::table('store_account_order as s')->leftJoin('store_franchisee as sf', RC_DB::raw('s.store_id'), '=', RC_DB::raw('sf.store_id'));
	
		$filter['keywords'] = !empty($_GET['keywords']) ? trim($_GET['keywords']) : '';
		$filter['start_time'] = !empty($_GET['start_time']) ? trim($_GET['start_time']) : '';
		$filter['end_time'] = !empty($_GET['end_time']) ? trim($_GET['end_time']) : '';
		$filter['merchant_keywords'] = !empty($_GET['merchant_keywords']) ? trim($_GET['merchant_keywords']) : '';
		
		$db->where('process_type', 'withdraw');
	
		if (!empty($filter['keywords'])) {
			$db->where(RC_DB::raw('s.order_sn'), 'like', '%'.mysql_like_quote($filter['keywords']).'%');
		}
		if (!empty($filter['start_time'])) {
			$db->where(RC_DB::raw('s.add_time'), '>=', RC_Time::local_strtotime($filter['start_time']));
		}
		if (!empty($filter['end_time'])) {
			$db->where(RC_DB::raw('s.add_time'), '<', RC_Time::local_strtotime($filter['end_time']));
		}
		if (!empty($filter['merchant_keywords'])) {
			$db->where(RC_DB::raw('sf.merchants_name'), '<', RC_Time::local_strtotime($filter['merchant_keywords']));
		}
	
		$type_count = $db->select(
				RC_DB::raw('SUM(IF(s.status = 1, 1, 0)) as wait_check'),
				RC_DB::raw('SUM(IF(s.status = 2, 1, 0)) as passed'),
				RC_DB::raw('SUM(IF(s.status = 3, 1, 0)) as refused'))
				->first();
		if (empty($type_count['wait_check'])) {
			$type_count['wait_check'] = 0;
		}
		if (empty($type_count['passed'])) {
			$type_count['passed'] = 0;
		}
		if (empty($type_count['refused'])) {
			$type_count['refused'] = 0;
		}
	
		$type = trim($_GET['type']);
		if (empty($type)) {
			$db->where(RC_DB::raw('s.status'), 1);
		}
		if ($type == 1) {
			$db->where(RC_DB::raw('s.status'), 2);
		}
		if ($type == 2) {
			$db->where(RC_DB::raw('s.status'), 3);
		}
	
		$count = $db->count();
		$page = new ecjia_page($count, 10, 5);
		$data = $db->select(RC_DB::raw('s.*'), RC_DB::raw('sf.merchants_name'))->take(10)->skip($page->start_id - 1)->orderBy(RC_DB::raw('s.add_time'), 'desc')->get();
	
		if (!empty($data)) {
			foreach ($data as $k => $v) {
				$data[$k]['amount'] = price_format($v['amount']);
				$data[$k]['add_time'] = RC_Time::local_date('Y-m-d H:i:s', $v['add_time']);
				$data[$k]['bank_name'] = !empty($v['bank_name']) ? '（'.$v['bank_name'].'）' : '';
				$bank_account_number = $this->substr_cut($v['account_number']);
				$data[$k]['account_number'] = $bank_account_number;
			}
		}
		return array('item' => $data, 'page' => $page->show(2), 'desc' => $page->page_desc(), 'count' => $type_count, 'filter' => $filter);
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