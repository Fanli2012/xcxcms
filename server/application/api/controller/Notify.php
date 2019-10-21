<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use think\Log;
use app\common\lib\Helper;
use app\common\lib\ReturnData;

class Notify extends Common
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    /**
     * 微信支付回调
     */
    public function wxpay_jsapi()
	{
        $res = 'SUCCESS'; //支付成功返回SUCCESS，失败返回FAILE
        
        //file_put_contents("1.txt",$GLOBALS['HTTP_RAW_POST_DATA']);
        Log::record('微信支付回调数据：'.$GLOBALS['HTTP_RAW_POST_DATA']);
        
        //获取通知的数据
		//$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml = file_get_contents("php://input");
        
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
		$post_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		//将附加参数转成数组
        if(isset($post_data['attach']) && !empty($post_data['attach']))
        {
            $get_arr = explode('&',$post_data['attach']);
            foreach($get_arr as $value)
            {
                $tmp_arr = explode('=',$value);
                $post_data[$tmp_arr[0]] = $tmp_arr[1];
            }
        }
        
        if($post_data['result_code'] != 'SUCCESS')
        {
			exit('FAILE');
		}
        
		$pay_money = $post_data['total_fee']/100; //支付金额
		$pay_time_timestamp = strtotime(date_format(date_create($post_data['time_end']), 'Y-m-d H:i:s')); //支付完成时间，时间戳格式
		$pay_time_date = date_format(date_create($post_data['time_end']), 'Y-m-d H:i:s'); //支付完成时间，date格式Y-m-d H:i:s
		//商户订单号
		$temp_out_trade_no = explode('-', $post_data['out_trade_no']);
		$out_trade_no = $temp_out_trade_no[0];
		//$post_data['transaction_id'] //微信支付订单号
		Log::record('充值订单号'.$out_trade_no);
        
		//附加参数pay_type:1充值支付,2订单支付
		if($post_data['pay_type'] == 1)
		{
            Log::record('充值支付');
			//获取充值支付记录
			$user_recharge = model('UserRecharge')->getOne(array('recharge_sn'=>$out_trade_no, 'status'=>0));
			if(!$user_recharge){ Log::record('充值记录不存在'); exit('FAILE'); }
			if($pay_money != $user_recharge['money']){ Log::record('充值金额不匹配'); exit('FAILE'); } //如果支付金额小于要充值的金额
			
			Db::startTrans();
			
			//更新充值支付记录状态
			$edit_user_recharge = model('UserRecharge')->edit(array('pay_time'=>$pay_time_timestamp,'update_time'=>time(),'status'=>1,'trade_no'=>$post_data['transaction_id'],'pay_money'=>$pay_money), array('recharge_sn'=>$out_trade_no,'status'=>0));
			if(!$edit_user_recharge)
			{
                Log::record('更新充值支付记录状态失败');
				Db::rollback();
				exit('FAILE');
			}
			
			//增加用户余额及余额记录
			$user_money_data['user_id'] = $user_recharge['user_id'];
			$user_money_data['type'] = 0;
			$user_money_data['money'] = $pay_money;
			$user_money_data['desc'] = '充值';
			$user_money = logic('UserMoney')->add($user_money_data);
			if($user_money['code'] != ReturnData::SUCCESS){ Db::rollback(); Log::record('用户余额记录失败'); exit('FAILE'); }
			
			Db::commit();
		}
		elseif($post_data['pay_type'] == 2)
		{
            Log::record('订单支付');
			//获取订单记录
			$order = model('order')->getOne(array('order_sn'=>$out_trade_no, 'order_status'=>0, 'pay_status'=>0));
			if(!$order){ Log::record('订单不存在'); exit('FAILE'); }
			if($pay_money != $order['order_amount']){ Log::record('订单金额不匹配'); exit('FAILE'); } //如果支付金额小于订单金额
			
			//修改订单状态
			$order_update_data['pay_status'] = 1;
			$order_update_data['pay_money'] = $pay_money; //支付金额
			$order_update_data['payment_id'] = 2;
			$order_update_data['pay_time'] = $pay_time_timestamp;
			$order_update_data['pay_name'] = 'wxpay_jsapi';
			$order_update_data['trade_no'] = $post_data['transaction_id'];
			$order_update_data['update_time'] = time();
			$edit_order = model('Order')->edit($order_update_data, array('order_sn'=>$out_trade_no, 'order_status'=>0, 'pay_status'=>0));
			if(!$edit_order)
			{
                Log::record('订单状态修改失败');
				exit('FAILE');
			}
		}
		elseif($post_data['pay_type'] == 3)
		{
			exit('FAILE');
		}
		elseif($post_data['pay_type'] == 4)
		{
			exit('FAILE');
		}
		else
		{
			exit('FAILE');
		}
		
		//file_put_contents("2.txt",$post_data['total_fee'].'--'.$out_trade_no.'--'.$post_data['attach'].'--'.$post_data['pay_type']);
		
        Log::record('支付成功');
        exit('SUCCESS');
	}
}