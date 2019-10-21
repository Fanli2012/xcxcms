<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\OrderLogic;
use app\common\model\Order as OrderModel;

class Order extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new OrderLogic();
    }
    
	//订单列表
    public function index()
	{
        //参数
        $limit = input('limit', 10);
        $offset = input('offset', 0);
        
		$where['user_id'] = $this->login_info['id'];
		//0或者不传表示全部，1待付款，2待发货,3待收货,4待评价(确认收货，交易成功),5退款/售后
        $status = input('status', '');
		if($status !== '' && $status != 0)
		{
			if($status == 1)
			{
				$where['order_status'] = 0;
				$where['pay_status'] = 0;
			}
			elseif($status == 2)
			{
				$where['order_status'] = 0;
				$where['shipping_status'] = 0;
				$where['pay_status'] = 1;
			}
			elseif($status == 3)
			{
				$where['order_status'] = 0;
				$where['shipping_status'] = 1;
				$where['pay_status'] = 1;
				$where['refund_status'] = 0;
			}
			elseif($status == 4)
			{
				$where['order_status'] = 3;
				$where['shipping_status'] = 2;
				$where['is_comment'] = 0;
				$where['refund_status'] = 0;
			}
			elseif($status == 5)
			{
				$where['order_status'] = 3;
				$where['refund_status'] = array('<>',0);
			}
		}
		
        $res = $this->getLogic()->getList($where, 'id desc', '*', $offset, $limit);
		
        if($res['count'] > 0)
        {
            foreach($res['list'] as $k=>$v)
            {
                if($res['list'][$k]['goods_list'])
                {
                    $goods_list = $res['list'][$k]['goods_list'];
                    foreach($goods_list as $key=>$value)
                    {
                        if($value['goods_img']){$goods_list[$key]['goods_img'] = sysconfig('CMS_SITE_CDN_ADDRESS').$value['goods_img'];}
                    }
                    
                    $res['list'][$k]['goods_list'] = $goods_list;
                }
            }
        }
        
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
    
	//订单详情
    public function detail()
	{
        //参数
        if(!checkIsNumber(input('id',null))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $id = input('id');
        $where['id'] = $id;
        $where['user_id'] = $this->login_info['id'];
        
        $res = $this->getLogic()->getOne($where);
		if(!$res){Util::echo_json(ReturnData::create(ReturnData::RECORD_NOT_EXIST));}
        
        if($res['goods_list'])
        {
            foreach($res['goods_list'] as $k=>$v)
            {
                if($v['goods_img']){$res['goods_list'][$k]['goods_img'] = sysconfig('CMS_SITE_CDN_ADDRESS').$v['goods_img'];}
            }
        }
		
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
    
    //添加
    public function add()
    {
        $data['user_address_id'] = input('user_address_id','');
        $data['user_bonus_id'] = input('user_bonus_id','');
        $data['shipping_costs'] = input('shipping_costs','');
        $data['message'] = input('message','');
        $data['place_type'] = input('place_type',2); //订单来源：1pc，2weixin，3app，4wap，5miniprogram
        
        //获取商品列表
        $data['cartids'] = input('cartids','');
        if($data['cartids']==''){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        
        if(Helper::isPostRequest())
        {
            $data['user_id'] = $this->login_info['id'];
            
            $res = $this->getLogic()->add($data);
			Util::echo_json($res);
        }
    }
    
    //修改
    public function edit()
    {
        if(!checkIsNumber(input('id',''))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $id = input('id');
        
        if(Helper::isPostRequest())
        {
            unset($_POST['id']);
            $where['id'] = $id;
            $where['user_id'] = $this->login_info['id'];
            
            $res = $this->getLogic()->edit($_POST,$where);
			Util::echo_json($res);
        }
    }
    
    //删除
    public function del()
    {
        if(!checkIsNumber(input('id',''))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $id = input('id');
        
        if(Helper::isPostRequest())
        {
            $where['id'] = $id;
            $where['user_id'] = $this->login_info['id'];
            
            $res = $this->getLogic()->del($where);
			Util::echo_json($res);
        }
    }
    
    //用户-取消订单
    public function user_cancel_order()
	{
        if(!checkIsNumber(input('id',''))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $id = input('id');
        
        $where['id'] = $id;
        $where['user_id'] = $this->login_info['id'];
        
        $res = $this->getLogic()->userCancelOrder($where);
		Util::echo_json($res);
    }
    
    //订单-余额支付
    public function order_yuepay()
	{
        if(!checkIsNumber(input('id',''))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $id = input('id');
        
        $where['id'] = $id;
        $where['user_id'] = $this->login_info['id'];
        
        $res = $this->getLogic()->orderYuepay($where);
		Util::echo_json($res);
    }
    
    //用户-确认收货
    public function user_receipt_confirm()
	{
        if(!checkIsNumber(input('id',''))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $id = input('id');
        
        $where['id'] = $id;
        $where['user_id'] = $this->login_info['id'];
        
        $res = $this->getLogic()->orderReceiptConfirm($where);
		Util::echo_json($res);
    }
    
    //用户-退款退货
    public function user_order_refund()
	{
        if(!checkIsNumber(input('id',null))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $id = input('id');
        
        $where['id'] = $id;
        $where['user_id'] = $this->login_info['id'];
        
        $res = $this->getLogic()->orderRefund($where);
		Util::echo_json($res);
    }
    
    //小程序订单支付
    public function wxminiprogram_pay()
	{
		//参数
        $order_id = input('id', '');
		
        //获取订单详情
		$where['id'] = $order_id;
        $where['user_id'] = $this->login_info['id'];
		$res = $this->getLogic()->getOne($where);
        if(!$res){Util::echo_json(ReturnData::create(ReturnData::RECORD_NOT_EXIST));}
        $order_info = $res;
		
		if(!($order_info['order_status']==0 && $order_info['pay_status']==0)){Util::echo_json(ReturnData::create(ReturnData::RECORD_NOT_EXIST, null, '订单已过期'));}
		
        //微信支付-start
        require_once EXTEND_PATH.'wxpay/WxPayMiniprogramConfig.php'; // 导入微信配置类
		require_once EXTEND_PATH.'wxpay/WxPayPubHelper.class.php'; // 导入微信支付类
        
		$body = '订单支付'; //订单详情
		$out_trade_no = $order_info['order_sn'].'-'.time(); //订单号，时间戳保证每次都不一样
		$total_fee = floatval($order_info['order_amount']*100); //价格0.01
        $attach = 'pay_type=2'; //附加数据，pay_type=2订单支付，示例：xxx=1&yyy=2
		$notify_url = url('api/notify/wxpay_jsapi'); //通知地址
		$wxconfig = \WxPayMiniprogramConfig::wxconfig();
        
		//=========步骤1：网页授权获取用户openid============
		$jsApi = new \JsApi_pub($wxconfig);
		//$openid = $jsApi->getOpenid();
		$openid = $this->login_info['openid']; //小程序支付
		//=========步骤2：使用统一支付接口，获取prepay_id============
		//使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub($wxconfig);
		//设置统一支付接口参数
		//设置必填参数
		//appid已填,商户无需重复填写
		//mch_id已填,商户无需重复填写
		//noncestr已填,商户无需重复填写
		//spbill_create_ip已填,商户无需重复填写
		//sign已填,商户无需重复填写
		$unifiedOrder->setParameter("openid","$openid");//微信用户openid，trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
		$unifiedOrder->setParameter("body","$body");//商品描述
		$unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号，这个每次都应不一样
		$unifiedOrder->setParameter("total_fee","$total_fee");//总金额
		$unifiedOrder->setParameter("attach","$attach"); //附加数据，选填，在查询API和支付通知中原样返回，可作为自定义参数使用，示例：a=1&b=2
        $unifiedOrder->setParameter("notify_url","$notify_url");//通知地址
		$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型,JSAPI，NATIVE，APP...
		$prepay_id = $unifiedOrder->getPrepayId();
		//=========步骤3：使用jsapi调起支付============
		$jsApi->setPrepayId($prepay_id);
		$jsApiParameters = $jsApi->getParameters();
		$jsApiParameters = json_decode($jsApiParameters, true);
		
		$return_data['jsApiParameters'] = array();
		if($jsApiParameters){$return_data['jsApiParameters'] = $jsApiParameters;}
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $return_data));
    }
	
    //商城支付宝APP支付
	public function order_alipay_app()
    {
        $id = input('id',null);
        if($id===null){return ReturnCode::create(ReturnCode::PARAMS_ERROR);}
        
        $order = DB::table('order')->where(['id'=>$id,'status'=>0,'user_id'=>Token::$uid])->first();
        if(!$order){return ReturnCode::create(ReturnCode::PARAMS_ERROR);}
        
        $order_pay = DB::table('order_pay')->where(['id'=>$order->payment_id])->first();
        if(!$order_pay){return ReturnCode::create(ReturnCode::PARAMS_ERROR);}
        
        require_once base_path('resources/org/alipay_app').'/AopClient.php';
        require_once base_path('resources/org/alipay_app').'/AlipayTradeAppPayRequest.php';
        
        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = config('alipay.app_alipay.appId');
        $aop->rsaPrivateKey = config('alipay.app_alipay.rsaPrivateKey');
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = config('alipay.app_alipay.alipayrsaPublicKey');
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"订单支付\"," 
                        . "\"subject\": \"订单支付\","
                        . "\"out_trade_no\": \"".$order_pay->sn."\","
                        . "\"total_amount\": \"".$order_pay->pay_amount."\","
                        . "\"timeout_express\": \"30m\"," 
                        . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                        . "}";
        $request->setNotifyUrl(config('app.url.apiDomain') . '/payment/notify/order_alipay/');
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        return ReturnCode::create(ReturnCode::SUCCESS,$response);//就是orderString 可以直接给客户端请求，无需再做处理。
    }
    
    //商城微信APP支付
	public function order_wxpay_app()
    {
        //参数
		$id = input('id',null);
        if($id===null){return ReturnCode::create(ReturnCode::PARAMS_ERROR);}
        
        $order_info = DB::table('order')->where(['id'=>$id,'status'=>0,'user_id'=>Token::$uid])->first();
        if(!$order_info){return ReturnCode::create(ReturnCode::PARAMS_ERROR);}
        
        $order_pay = DB::table('order_pay')->where(['id'=>$order_info->payment_id])->first();
        if(!$order_pay){return ReturnCode::create(ReturnCode::PARAMS_ERROR);}
        
		//1.配置
		$options = config('weixin.app');
        
		$app = new \EasyWeChat\Foundation\Application($options);
		$payment = $app->payment;
		$out_trade_no = $order_pay->sn;
        
		//2.创建订单
		$attributes = [
			'trade_type'       => 'APP', // JSAPI，NATIVE，APP...
			'body'             => '订单支付',
			'detail'           => '订单支付',
			'out_trade_no'     => $out_trade_no,
			'total_fee'        => $order_pay->pay_amount*100, // 单位：分
			'notify_url'       => config('app.url.apiDomain').'payment/notify/app_order_weixin_pay/', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
			//'openid'           => '当前用户的 openid', // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
			// ...
		];
        
		$order = new \EasyWeChat\Payment\Order($attributes);
        
		//3.统一下单
		$result = $payment->prepare($order);
        
		if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS')
		{
			$prepayId = $result->prepay_id;
			$res = $payment->configForAppPayment($prepayId);
		}
        
		$res['out_trade_no'] = $out_trade_no;

		return ReturnCode::create(ReturnCode::SUCCESS,$res);
    }
}