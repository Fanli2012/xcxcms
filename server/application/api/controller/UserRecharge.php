<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\UserRechargeLogic;
use app\common\logic\UserRecharge as UserWithdrawModel;

class UserRecharge extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new UserRechargeLogic();
    }
    
    //列表
    public function index()
	{
        //参数
        $where = array();
        $limit = input('limit',10);
        $offset = input('offset', 0);
        $orderby = input('orderby','id desc');
        if (input('status', '') != '' && input('status')!=-1) { $where['status'] = input('status'); }
        $where['user_id'] = $this->login_info['id'];
		
        $res = $this->getLogic()->getList($where, $orderby, '*', $offset, $limit);
		
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
    
    //详情
    public function detail()
	{
        //参数
        if(!checkIsNumber(input('id/d',0))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
        $where['id'] = input('id');
        $where['user_id'] = $this->login_info['id'];
		
		$res = $this->getLogic()->getOne($where);
        if(!$res){Util::echo_json(ReturnData::create(ReturnData::RECORD_NOT_EXIST));}
        
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
    
    //添加
    public function add()
    {
        if(Helper::isPostRequest())
        {
			$_POST['user_id'] = $this->login_info['id'];
            $res = $this->getLogic()->add($_POST);
            
            Util::echo_json($res);
        }
    }
    
    //修改
    public function edit()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
            $where['id'] = input('id');
            unset($_POST['id']);
			$where['user_id'] = $this->login_info['id'];
            $res = $this->getLogic()->edit($_POST,$where);
            
            Util::echo_json($res);
        }
    }
    
    //删除
    public function del()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
            $where['id'] = input('id');
            $where['user_id'] = $this->login_info['id'];
            $res = $this->getLogic()->del($where);
            
            Util::echo_json($res);
        }
    }
	
    //小程序用户充值
    public function wxminiprogram_pay()
	{
        $id = input('id', '');
        if($id == ''){$this->error('参数错误');}
        
        //获取充值记录详情
		$where['id'] = input('id');
        $where['user_id'] = $this->login_info['id'];
		$res = $this->getLogic()->getOne($where);
        if(!$res){Util::echo_json(ReturnData::create(ReturnData::RECORD_NOT_EXIST));}
        $user_recharge = $res;
        
        //微信支付-start
		require_once EXTEND_PATH.'wxpay/WxPayMiniprogramConfig.php'; // 导入微信配置类
		require_once EXTEND_PATH.'wxpay/WxPayPubHelper.class.php'; // 导入微信支付类
        
		$body = '充值';//订单详情
		$out_trade_no = $user_recharge['recharge_sn'].'-'.time(); //订单号，时间戳保证每次都不一样
		$total_fee = floatval($user_recharge['money']*100);//价格0.01
        $attach = 'pay_type=1'; //附加数据，pay_type=1充值支付，示例：xxx=1&yyy=2
		$notify_url = url('api/notify/wxpay_jsapi'); //通知地址
		$wxconfig= \WxPayMiniprogramConfig::wxconfig(); //微信小程序支付配置
        
		//=========步骤1：网页授权获取用户openid============
		$jsApi = new \JsApi_pub($wxconfig);
		//$openid = $jsApi->getOpenid(); //网页公众号支付
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
		$unifiedOrder->setParameter("openid","$openid");//微信用户
		$unifiedOrder->setParameter("body","$body");//商品描述
		$unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号，这个每次都应不一样
		$unifiedOrder->setParameter("total_fee","$total_fee");//总金额
		$unifiedOrder->setParameter("attach","$attach"); //附加数据，选填，在查询API和支付通知中原样返回，可作为自定义参数使用，示例：a=1&b=2
        $unifiedOrder->setParameter("notify_url","$notify_url");//通知地址
		$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
		$prepay_id = $unifiedOrder->getPrepayId();
		//=========步骤3：使用jsapi调起支付============
		$jsApi->setPrepayId($prepay_id);
		$jsApiParameters = $jsApi->getParameters();
		$jsApiParameters = json_decode($jsApiParameters, true);
		
		$return_data['jsApiParameters'] = array();
		if($jsApiParameters){$return_data['jsApiParameters'] = $jsApiParameters;}
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $return_data));
    }
}