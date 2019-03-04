<?php
namespace payment\wxpay_js;
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once "WxPayApi.php";
require_once 'WxPayNotify.php';
require_once 'log.php';



class PayNotifyCallBack extends WxPayNotify
{
   

/**
 * 获取账户类型
 * @param int $status
 * @return int 账户类型 ，false 未获取到
 * @author 王世凡 <wangshifan116@sina.com>
 */
public function get_account_type($account_type = null) {
    if (!isset($account_type)) {
        return false;
    }
    $arr = array('usable_account'=>1, 'current_deposit_account'=>2, 'frozen_account'=>3, 'done_account'=>4, 'transfer_limit'=>5, 'current_account'=>6, 'punish_limit_a'=>7, 'punish_limit_b'=>8, 'damaged_account'=>9, 'current_punish_account'=>10, 'history_punish_account'=>11, 'current_ubtransfer'=>12);
    return $arr[$account_type];
}
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		$map['order_sn'] = $data['out_trade_no'];//商户订单号，也就是优加系统中的充值流水号
		
		if ($data['result_code'] == 'SUCCESS') {
			//做充值成功处理
			$rechargedata['is_paid'] = 1;
			$rechargedata['pay_time'] = strtotime($data['time_end']);
			M('Recharge')->where($map)->save($rechargedata);
		}else if ($data['result_code'] == 'FAIL') 
		{
			//做充值失败处理
			$rechargedata['is_paid'] = 0;
			$rechargedata['pay_time'] = strtotime($data['time_end']);
			M('Recharge')->where($map)->save($rechargedata);
		}
		
		$result['transaction_id'] =$data["transaction_id"];
		$result['total_fee'] = $data['total_fee']/100;
		$result['cash_fee'] = $data['cash_fee']/100;
		$result['trans_status'] = $data['result_code'];//业务结果，success或者fail
		$result['end_time'] = strtotime($data['time_end']);//交易完成时间
		$saveid = M('Recharge_log')->where($map)->save($result);
		
		
		if (!empty($saveid) && $data['result_code'] == 'SUCCESS') {
			$uid = M('Recharge_log')->where($map)->getField('uid');
			$account = M('account')->where(array('uid'=>$uid))->find();
			
			$platamount = M('account')->where(array('uid'=>1))->getField('current_account');
			$platlogdata['uid'] = 1;
			$platlogdata['income'] = $data['total_fee']/100;//分转化为单位为元
			$platlogdata['amount'] = $platamount + $data['total_fee']/100;
			$platlogdata['add_time'] = time();
			$platlogdata['process_type'] = $this->get_account_type('current_account');//平台现金账户
			$platlogdata['payer'] = $uid;
			$platlogdata['postscript'] = '因用户账户充值增加';
			$platlogdata['optcode'] = 2;
			$platlogdata['sn'] = $data['out_trade_no'];
			$platlogdata['log_type'] = 1;//1为入账
			
			M('Account_log')->add($platlogdata);
			M('account')->where(array('uid'=>1))->setInc('current_account',$data['total_fee']/100);
			
                        $amount = $data['total_fee']/5;//充值的优币数
                        $to_usable_account = 0;//充值的优币到可用优币数
                        $to_current_account = 0;//充值的优币到当前保证金数
                        
			if ($account['current_deposit_account'] >= $account['deposit_limit']) {
                                        $to_usable_account = $amount;
                                        $to_current_account = 0;
					$accountlogdata['uid'] = $uid;
					$accountlogdata['income'] = $amount;
					$accountlogdata['amount'] = $account['usable_account'] + $amount;
					$accountlogdata['add_time'] = time();
					$accountlogdata['process_type'] = $this->get_account_type('usable_account');
					$accountlogdata['payer'] = $uid;
					$accountlogdata['postscript'] = '账户充值';
					$accountlogdata['optcode'] = 2;
					$accountlogdata['sn'] = $data['out_trade_no'];
					$accountlogdata['log_type'] = 1;//1为入账
					
					
					M('Account_log')->add($accountlogdata);
					M('account')->where(array('uid'=>$uid))->setInc('usable_account',$to_usable_account);
                                        
					
					
			}else {
				if (($account['deposit_limit'] - $account['current_deposit_account']) <= $amount) {
					
					$to_current_account = ($account['deposit_limit'] - $account['current_deposit_account']);
					$to_usable_account = $amount - $to_current_account;
					
					$accountlogdataone['uid'] = $uid;
					$accountlogdataone['income'] = $to_current_account;
					$accountlogdataone['amount'] = $account['current_deposit_account'] + $to_current_account;
					$accountlogdataone['add_time'] = time();
					$accountlogdataone['process_type'] = $this->get_account_type('current_deposit_account');
					$accountlogdataone['payer'] = $uid;
					$accountlogdataone['postscript'] = '账户充值';
					$accountlogdataone['optcode'] = 2;
					$accountlogdataone['sn'] = $data['out_trade_no'];
					$accountlogdataone['log_type'] = 1;
					M('Account_log')->add($accountlogdataone);
					M('Account')->where(array('uid'=>$uid))->setInc('current_deposit_account',$to_current_account);
					
					
					$accountlogdatatwo['uid'] = $uid;
					$accountlogdatatwo['income'] = $to_current_account;
					$accountlogdatatwo['amount'] = $account['usable_account'] + $to_current_account;
					$accountlogdatatwo['add_time'] = time();
					$accountlogdatatwo['process_type'] = $this->get_account_type('usable_account');
					$accountlogdatatwo['payer'] = $uid;
					$accountlogdatatwo['postscript'] = '账户充值';
					$accountlogdatatwo['optcode'] = 2;
					$accountlogdatatwo['sn'] = $data['out_trade_no'];
					$accountlogdatatwo['log_type'] = 1;
					M('Account_log')->add($accountlogdatatwo);
					M('Account')->where(array('uid'=>$uid))->setInc('usable_account',$to_usable_account);
					
					
					
				}else {
					$to_usable_account = 0;
                                        $to_current_account = $amount;
					$accountlogdatathree['uid'] = $uid;
					$accountlogdatathree['income'] = $to_current_account;
					$accountlogdatathree['amount'] = $account['current_deposit_account'] + $to_current_account;
					$accountlogdatathree['add_time'] = time();
					$accountlogdatathree['process_type'] = $this->get_account_type('current_deposit_account');
					$accountlogdatathree['payer'] = $uid;
					$accountlogdatathree['postscript'] = '账户充值';
					$accountlogdatathree['optcode'] = 2;
					$accountlogdatathree['sn'] = $data['out_trade_no'];
					$accountlogdatathree['log_type'] = 1;
					M('Account_log')->add($accountlogdatathree);
					M('Account')->where(array('uid'=>$uid))->setInc('current_deposit_account',$to_current_account);
				}
				
				
			}
                        $receivables = throughReceivables($uid, 43, 'CZ');
			//往充值优币去向表写入一条数据
                        M('Recharge_to')->add(array('order_sn' => $data['out_trade_no'], 'uid'=>$uid, 'amount' => $amount, 'usable_account' => $to_usable_account, 'current_account' => $to_current_account, 'receivable_account' => $receivables, 'add_time' => time()));
			$content = '你于' . date('Y-m-d H:i', $rs['add_time']) .' 已成功充值' . $amount . '优币。其中' . $to_usable_account . '优币充入可用优币，' . $to_current_account . '优币充入当前保证金，' . $receivables . '优币抵扣应收优币。';
                        $message = array('sender'=>'administrator', 'receiver'=>get_usernum($uid), 'content'=>$content, 'send_time'=>NOW_TIME, 'suid'=>1, 'ruid'=>$uid);
                        M('Message')->add($message);
			return true;
		}else {
			return false;
		}
                
                }
}


