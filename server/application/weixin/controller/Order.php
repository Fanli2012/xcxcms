<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class Order extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    //订单列表
    public function index()
	{
		//参数
        $pagesize = 10;
        $offset = 0;
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        $status = input('status', -1);
        //获取订单列表
        $get_data = array(
            'limit'  => $pagesize,
            'offset' => $offset,
            'status' => $status, //0或者不传表示全部，1待付款，2待发货,3待收货,4待评价,5退款/售后
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/order/index';
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
                    $html .= '<div class="floor mt10">';
                    $html .= '<a href="'.url('order/detail').'?id='.$v['id'].'">';
                    $html .= '<div class="tit_h">单号:'.$v['id'].'<span class="fr">'.$v['status_text'].'</span></div>';
                    $html .= '<ul class="goodslist">';
					if($v['goods_list'])
					{
						foreach($v['goods_list'] as $key => $value)
						{
							$html .= '<li>';
							$html .= '<img src="'.$value['goods_img'].'" onerror="this.src=\''.sysconfig('CMS_BASEHOST').'/images/weixin/no_pic.jpg\'">';
							$html .= '<p><b>'.$value['goods_name'].'</b><span>￥'.$value['goods_price'].'<i>x'.$value['goods_number'].'</i></span></p>';
							$html .= '</li>';
						}
					}
					$html .= '</ul></a>';
					$html .= '<p class="des">合计: ￥'.$v['order_amount'].' <small>(含运费:￥'.$v['shipping_fee'].')</small></p>';
					$html .= '<div class="tag"><!--';
					if($v['order_status'] == 3 && $v['refund_status'] == 0){ $html .= '<a href="javascript:refund_order('.$v['id'].');">申请退款</a>'; }
					if(($v['order_status'] == 3 && $v['refund_status'] == 0) || $v['order_status'] == 1 || $v['order_status'] == 2){ $html .= '<a href="javascript:del_order('.$v['id'].');">删除</a>'; }
					$html .= '-->';
					if($v['order_status'] == 0 && $v['pay_status'] ==0){ $html .= '<a href="javascript:cancel_order('.$v['id'].');">取消订单</a>'; }
					if($v['order_status'] == 0 && $v['pay_status'] ==0){ $html .= '<a href="'.url('order/pay').'?id='.$v['id'].'">付款</a>'; }
					if($v['order_status'] == 0 && $v['refund_status'] == 0 && $v['shipping_status'] == 1 && $v['pay_status'] == 1){ $html .= '<a href="http://m.kuaidi100.com/index_all.html?type='.$v['shipping_name'].'&postid='.$v['shipping_sn'].'#result">查看物流</a>'; }
					if(($v['order_status'] == 0 && $v['shipping_status'] == 0 && $v['pay_status'] == 1) || ($v['order_status'] == 0 && $v['refund_status'] == 0 && $v['shipping_status'] == 1 && $v['pay_status'] == 1)){ $html .= '<a href="javascript:done_order('.$v['id'].');">确认收货</a>'; }
					if(($v['order_status'] == 3 && $v['refund_status'] == 0) && $v['is_comment']==0){ $html .= '<a class="activate" href="'.url('order/comment').'?id='.$v['id'].'">评价</a>'; }
                    $html .= '</div>';
                }
            }
            
    		exit(json_encode($html));
    	}
        
		$this->assign($assign_data);
        return $this->fetch();
    }
    
    //订单详情
    public function detail()
	{
		//参数
        $id = input('id', '');
        
        $get_data = array(
            'id'  => $id,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/order/detail';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['post'] = $res['data'];
        if(empty($assign_data['post'])){$this->error('订单不存在');}
        
		$this->assign($assign_data);
        return $this->fetch();
    }
    
	//订单评价
    public function comment()
	{
        if(Helper::isPostRequest())
        {
            if($_POST['comment'])
            {
                foreach($_POST['comment'] as $k=>$v)
                {
                    $_POST['comment'][$k]['comment_type'] = 0;
                    $_POST['comment'][$k]['comment_rank'] = 5;
                }
            }
            else
            {
                $this->error('评价失败');
            }
            
            $post_data = array(
                'order_id' => $_POST['order_id'],
                'comment' => json_encode($_POST['comment']),
                'access_token' => $this->login_info['token']['token']
            );
            $url = sysconfig('CMS_API_URL').'/comment/batch_add_goods_comment';
            $res = Util::curl_request($url, $post_data, 'POST');
            if($res['code'] != ReturnData::SUCCESS){$this->error($res['msg']);}
            
            $this->success('评价成功', url('order/index'));
        }
        
        $id = input('id','');
        if($id==''){$this->error('您访问的页面不存在或已被删除');}
        
        $get_data = array(
            'id' => $id,
            'order_status' => 3,
            'refund_status' => 0,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/order/detail';
		$res = Util::curl_request($url,$get_data,'GET');
        $assign_data['post'] = $res['data'];
        if(empty($assign_data['post'])){$this->error('您访问的页面不存在或已被删除');}
        if($assign_data['post']['is_comment'] == 1){$this->error('您已评价', url('order/index'));}
		
		$this->assign($assign_data);
        return $this->fetch();
    }
    
    //订单支付
    public function pay()
	{
		//参数
		$id = input('id', ''); //要支付的订单ID
        //获取订单详情
        $get_data = array(
            'id' => $id, //要支付的订单id
            'order_status' => 0,
            'pay_status' => 0,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/order/detail';
		$res = Util::curl_request($url,$get_data,'GET');
        if($res['code']!=ReturnData::SUCCESS || empty($res['data'])){$this->error('订单不存在或已过期');}
        $assign_data['order'] = $res['data'];
        
        //获取会员信息
        $get_data = array(
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/user/detail';
		$res = Util::curl_request($url, $get_data, 'GET');
		$this->login_info = array_merge($this->login_info, $res['data']);
		session('weixin_user_info', $this->login_info);
        
        //判断余额是否足够
        $is_balance_enough = 1; //足够
        if($assign_data['order']['order_amount'] > $this->login_info['money']){$is_balance_enough = 0;}
        $assign_data['is_balance_enough'] = $is_balance_enough;
        
		$this->assign($assign_data);
        return $this->fetch();
	}
    
    public function dopay()
	{
		//参数
        $order_id = input('order_id',''); //订单ID
        $payment_id = input('payment_id',''); //支付方式
        if($order_id == ''){$this->error('参数错误');}
        
        $url = '';
        if($payment_id == 1) //余额支付
        {
            $url = url('order/yuepay').'?order_id='.$order_id;
        }
        elseif($payment_id == 2) //微信支付
        {
            $url = url('order/wxpay').'?order_id='.$order_id;
        }
        else
		{
			$this->error('请选择支付方式');
		}
		
        header('Location: '.$url);exit;
    }
    
    //订单余额支付
    public function yuepay()
	{
		//参数
        $order_id = input('order_id','');

        //修改订单状态
        $post_data = array(
            'id' => $order_id,
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/order/order_yuepay';
		$res = Util::curl_request($url, $post_data, 'POST');
        if($res['code'] != ReturnData::SUCCESS){ $this->error('支付失败：'.$res['msg']); }
        
        $this->success('支付成功', url('order/index'));
    }
    
    //订单-微信支付
    public function wxpay()
	{
		//参数
        $order_id = input('order_id', '');
        
        //获取订单详情
        $get_data = array(
            'id' => $order_id, //要支付的订单ID
            'access_token' => $this->login_info['token']['token']
		);
        $url = sysconfig('CMS_API_URL').'/order/detail';
		$res = Util::curl_request($url, $get_data, 'GET');
        if($res['code'] != ReturnData::SUCCESS || empty($res['data'])){ $this->error('订单不存在'); }
		$assign_data['order'] = $res['data'];
		if(!($assign_data['order']['order_status']==0 && $assign_data['order']['pay_status']==0)){ $this->error('订单已过期'); }
		
        //微信支付-start
        require_once EXTEND_PATH.'wxpay/WxPayConfig.php'; // 导入微信配置类
		require_once EXTEND_PATH.'wxpay/WxPayPubHelper.class.php'; // 导入微信支付类
        
		$body = '订单支付'; //订单详情
		$out_trade_no = $assign_data['order']['order_sn'].'-'.time(); //订单号，时间戳保证每次都不一样
		$total_fee = floatval($assign_data['order']['order_amount']*100); //价格0.01
        $attach = 'pay_type=2'; //附加数据，pay_type=2订单支付，示例：xxx=1&yyy=2
		$notify_url = url('api/notify/wxpay_jsapi'); //通知地址
		$wxconfig = \WxPayConfig::wxconfig();
        
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
        
		$assign_data['jsApiParameters'] = $jsApiParameters;
        $assign_data['returnUrl'] = url('order/index'); //支付完成要跳转的url，跳转到用户订单列表页面
        
		$this->assign($assign_data);
        return $this->fetch();
    }
}