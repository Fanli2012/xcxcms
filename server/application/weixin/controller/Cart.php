<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\model\UserAddress as UserAddressModel;

class Cart extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    //购物车列表
    public function index()
	{
        //购物车列表
        $get_data = array(
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/cart/index';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['list'] = $res['data'];
        
        //猜你喜欢商品列表
        $get_data = array(
            'limit'  => 4,
            'orderby'=> 1,
            'offset' => 0
		);
        $url = sysconfig('CMS_API_URL').'/goods/index';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['like_goods_list'] = $res['data']['list'];
		
		$this->assign($assign_data);
        return $this->fetch();
	}
    
    //购物车结算
    public function checkout()
	{
		$cartids = input('cartids', '');
        $assign_data['cartids'] = $cartids;
		
        //获取会员信息
        $get_data = array(
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user/detail';
		$res = Util::curl_request($url, $get_data, 'GET');
		$this->login_info = array_merge($this->login_info, $res['data']);
		session('weixin_user_info', $this->login_info);
        
        //支付方式列表
        $url = sysconfig('CMS_API_URL').'/payment/index';
		$res = Util::curl_request($url, array(),'GET');
        $assign_data['payment_list'] = $res['data'];
        
        //收货地址列表
        $get_data = array(
            'limit'  => 10,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user_address/index';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['address_list'] = $res['data']['list'];
        
        //用户默认收货地址
        $assign_data['user_default_address'] = array();
		if($res['data']['list'])
		{
			foreach($res['data']['list'] as $k=>$v)
			{
				if($v['is_default']==UserAddressModel::USER_ADDRESS_IS_DEFAULT){$assign_data['user_default_address'] = $v;}
			}
			
			if(!$assign_data['user_default_address']){$assign_data['user_default_address'] = $res['data']['list'][0];}
		}
		
        //购物车结算商品列表
        $get_data = array(
            'cartids' => $cartids,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/cart/cart_checkout_goods_list';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['checkout_goods'] = $res['data'];
        if(empty($assign_data['checkout_goods']['list'])){$this->error('没有要结算的商品');}
        
        //判断余额是否足够支付订单
        $is_balance_enough = 1; //足够
        if($assign_data['checkout_goods']['total_price']>$this->login_info['money']){$is_balance_enough = 0;}
        $assign_data['is_balance_enough'] = $is_balance_enough;
        
        //获取用户优惠券列表
        $get_data = array(
            'min_amount' => $assign_data['checkout_goods']['total_price'],
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user_bonus/user_available_bonus_list';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['user_bonus_list'] = $res['data'];
        
		$this->assign($assign_data);
        return $this->fetch();
    }
    
    //生成订单
    public function cart_done()
	{
        $cartids = input('cartids',''); //购物车商品ID，8_9
        $user_address_id = input('user_address_id',''); //收货地址ID
        //$payid = input('payid',''); //支付方式：1余额支付，2微信，3支付宝
        $user_bonus_id = input('user_bonus_id', 0); //优惠券ID，0没有优惠券
        $shipping_costs = input('shipping_costs',''); //运费
        $message = input('message',''); //买家留言
        
        if($user_address_id == ''){$this->error('请选择收货地址');}
        //if($payid == ''){$this->error('请选择支付方式');}
        if($cartids == ''){$this->error('没有要结算的商品');}
        
        //订单提交
        $post_data = array(
            'cartids' => $cartids,
            'user_address_id' => $user_address_id,
            //'payid' => $payid,
            'user_bonus_id' => $user_bonus_id,
            'shipping_costs' => $shipping_costs,
            'message' => $message,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/order/add';
		$res = Util::curl_request($url,$post_data,'POST');
        if($res['code'] != ReturnData::SUCCESS)
        {
            $this->error($res['msg']);
    	}
		
		header("Location: ".url('order/pay').'?id='.$res['data']);exit;
    }
}