<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 19/6/21 021
 * Time: 10:00
 */

namespace Ecjia\App\Commission;

use RC_DB;
use Ecjia\App\Commission\Models\StoreAccountModel;
use Ecjia\App\Commission\Models\StoreAccountOrderModel;
use Ecjia\App\Commission\Models\StoreAccountLogModel;
use Ecjia\App\Commission\StoreAccountOrder;
use RC_Time;
use ecjia_error;
/*
 * 商家账户（余额）操作类
 * store_account_order -> store_account -> store_account_log
 * 可用余额=money-保证金(deposit)
 * */
class StoreAccount
{

    public $store_id;
    public $account_info;
    public $change_type;
    public $process_type;
    public $change_money;
    public $change_desc;

    public function __construct($store_id)
    {
        $this->store_id = $store_id;
        $this->account_info = $this->getStoreAccount();
    }

    public function getBalance() {
        $balance = array_get($this->account_info, 'money', 0) - array_get($this->account_info, 'deposit', 0);
        return $balance < 0 ? 0 : $balance;
    }

    public function getStoreAccount() {
        return collect(StoreAccountModel::where('store_id', $this->store_id)->first())->toArray();
    }

    //下单扣费并记录
    public function order($order_sn, $order_amount) {
        $this->change_type = $this->process_type = StoreAccountOrder::PROCESS_TYPE_ORDER;
        $this->change_desc = '订单 '. $order_sn;

        if(empty($this->store_id) || empty($order_amount) ) {
            return new ecjia_error('invalid_parameter_store_account', __('参数无效', 'commission'));
        }

        if($order_amount > $this->getBalance()) {
            return new ecjia_error('invalid_parameter_store_account', __('账户余额不足，请先充值', 'commission'));
        }

        $order_amount = $order_amount * -1;
        $data['order_sn'] = $order_sn;
        $data['amount'] = $order_amount;
        $data['pay_time'] = $data['add_time'] = RC_Time::gmtime();
        $data['pay_status'] = 1;
        $data['status'] = 2;

        if($this->insertStoreAccountOrder($data)) {
            //改动账户
            return $this->updateStoreAccount($order_amount);
        }

        return false;

    }

    //订单表
    protected function insertStoreAccountOrder($data) {

        $data['order_sn'] = empty($data['order_sn']) ? ecjia_order_store_account_sn() : $data['order_sn'];
        $data['store_id'] = $this->store_id;
        return StoreAccountOrderModel::insert($data);
    }

    protected function updateStoreAccount($money = 0) {
        $info = $this->account_info;

        if(empty($info)) {
            StoreAccountModel::insert(['store_id' => $this->store_id]);
        }

//        if ($change_type == 'withdraw' && $info['money'] - abs($money) < $info['deposit']) {
//            return new ecjia_error('withdraw_error', __('提现金额过大，余额需不低于保证金', 'commission'));
//        }

        $info['money'] = $info['money'] ? $info['money'] : 0;
        $info['frozen_money'] = $info['frozen_money'] ? $info['frozen_money'] : 0;
        $data = array(
            'money_before' => $info['money'] ? $info['money'] : 0,
            'money' => $info['money'] + $money,
        );
//        if($change_type == 'withdraw') {
//            $data['frozen_money'] = $info['frozen_money'] + $money;
//            $data['frozen_money'] = $data['frozen_money'] ? $data['frozen_money'] : 0;
//        }

        if(StoreAccountModel::where('store_id', $this->store_id)->update($data)) {
            $log = array(
                'store_money' => $data['money'],
                'money' => $money,
                'change_desc' => $this->change_desc,
            );
            return $this->insertStoreAccountLog($log);
        }

    }

    protected function insertStoreAccountLog($data) {

        if(empty($this->store_id) || empty($this->change_type)) {
            return new ecjia_error('invalid_parameter_store_account', __('参数无效', 'commission'));
        }
        $data['store_id'] = $this->store_id;
        $data['change_type'] = $this->change_type;
        $data['change_time'] = RC_Time::gmtime();

        return StoreAccountLogModel::insert($data);
    }


}