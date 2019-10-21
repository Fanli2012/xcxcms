<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class UserRecharge extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
	
    //充值明细列表
    public function index()
	{
		//参数
        $pagesize = 10;
        $offset = 0;
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        //获取充值明细列表
        $get_data = array(
            'limit'  => $pagesize,
            'offset' => $offset,
            'status' => 1,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user_recharge/index';
		$res = Util::curl_request($url, $get_data, 'GET');
        $assign_data['list'] = $res['data']['list'];
        //总页数
        $assign_data['totalpage'] = ceil($res['data']['count']/$pagesize);
        
        if(isset($_REQUEST['page_ajax']) && $_REQUEST['page_ajax']==1)
        {
    		$html = '';
            
            if($res['data']['list'])
            {
                foreach($res['data']['list'] as $k => $v)
                {
                    $html .= '<li>';
                    $html .= '<span class="green">+ '.$v['money'].'</span>';
                    $html .= '<div class="info"><p class="tit">充值</p>';
                    $html .= '<p class="time">'.date('Y-m-d H:i:s', $v['add_time']).'</p></div>';
                    $html .= '</li>';
                }
            }
            
    		exit(json_encode($html));
    	}
        
		$this->assign($assign_data);
        return $this->fetch();
    }
	
    //充值
    public function add()
	{
		return $this->fetch();
    }
	
    //用户充值第二步，支付
    public function detail()
	{
        $id = input('id', '');
        if($id == ''){$this->error('参数错误');}
        
        //获取充值记录详情
        $get_data = array(
            'id' => $id,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user_recharge/detail';
		$res = Util::curl_request($url, $get_data, 'GET');
        $user_recharge = $assign_data['post'] = $res['data'];
        
        //微信支付-start
		require_once EXTEND_PATH.'wxpay/WxPayConfig.php'; // 导入微信配置类
		require_once EXTEND_PATH.'wxpay/WxPayPubHelper.class.php'; // 导入微信支付类
        
		$body = '充值';//订单详情
		$out_trade_no = $user_recharge['recharge_sn'].'-'.time(); //订单号，时间戳保证每次都不一样
		$total_fee = floatval($user_recharge['money']*100);//价格0.01
        $attach = 'pay_type=1'; //附加数据，pay_type=1充值支付，示例：xxx=1&yyy=2
		$notify_url = url('api/notify/wxpay_jsapi'); //通知地址
		$wxconfig= \WxPayConfig::wxconfig(); //微信公众号支付配置
        
		//=========步骤1：网页授权获取用户openid============
		$jsApi = new \JsApi_pub($wxconfig);
		$openid = $jsApi->getOpenid();
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
        
		$assign_data['jsApiParameters'] = $jsApiParameters;
        $assign_data['returnUrl'] = url('user_recharge/index'); //支付完成要跳转的url
        
		$this->assign($assign_data);
        return $this->fetch();
    }
    
}