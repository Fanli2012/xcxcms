<?php

namespace app\common\logic;

use think\Loader;
use think\Validate;
use think\Db;
use app\common\lib\ReturnData;
use app\common\model\Order;
use app\common\model\UserBonus;

class OrderLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new Order();
    }

    public function getValidate()
    {
        return Loader::validate('Order');
    }

    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = '', $limit = '')
    {
        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);

        if ($res['count'] > 0) {
            foreach ($res['list'] as $k => $v) {
                //$res['list'][$k] = $this->getDataView($v);
                $res['list'][$k] = $res['list'][$k]->append(array('status_text', 'country_name', 'province_name', 'city_name', 'district_name', 'invoice_text', 'place_type_text', 'district_name'))->toArray();
                //订单商品列表
                $order_goods = model('OrderGoods')->getAll(array('order_id' => $v['id']));
                $res['list'][$k]['goods_list'] = $order_goods;
            }
        }

        return $res;
    }

    //分页html
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getPaginate($where, $order, $field, $limit);

        $res = $res->each(function ($item, $key) {
            //$item = $this->getDataView($item);
            $item = $item->append(array('status_text', 'country_name', 'province_name', 'city_name', 'district_name', 'invoice_text', 'place_type_text', 'district_name'))->toArray();
            return $item;
        });

        return $res;
    }

    //全部列表
    public function getAll($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getAll($where, $order, $field, $limit);

        if ($res) {
            foreach ($res as $k => $v) {
                //$res[$k] = $this->getDataView($v);
                $res[$k] = $res[$k]->append(array('status_text', 'country_name', 'province_name', 'city_name', 'district_name', 'invoice_text', 'place_type_text', 'district_name'))->toArray();
            }
        }

        return $res;
    }

    //详情
    public function getOne($where = array(), $field = '*')
    {
        $res = $this->getModel()->getOne($where, $field);
        if (!$res) {
            return false;
        }

        //$res = $this->getDataView($res);
        $res = $res->append(array('goods_list', 'user', 'status_text', 'country_name', 'province_name', 'city_name', 'district_name', 'invoice_text', 'place_type_text', 'district_name'))->toArray();

        return $res;
    }

    //添加
    public function add($data = array(), $type = 0)
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        //添加时间、更新时间
        if (!(isset($data['add_time']) && !empty($data['add_time']))) {
            $data['add_time'] = time();
        }
        if (!(isset($data['update_time']) && !empty($data['update_time']))) {
            $data['update_time'] = time();
        }

        //验证数据
        $validate = new Validate([
            ['cartids', 'require|max:30', '购物车商品ID不能为空|购物车商品ID不能超过30个字符'],
            ['user_id', 'require|number|max:11', '用户ID必填|用户ID必须是数字|用户ID格式不正确'],
            ['user_bonus_id', 'number|max:11', '优惠券ID必须为数字|优惠券ID格式不正确'],
            ['user_address_id', 'require|number|max:11', '收货地址不能为空|收货地址必须是数字|收货地址格式不正确'],
            ['shipping_costs', 'regex:/^\d{0,10}(\.\d{0,2})?$/', '运费格式不正确'],
            ['message', 'max:240', '买家留言不能超过240个字符'],
            ['place_type', 'in:0,1,2,3,4,5', '订单来源：1pc，2weixin，3app，4wap，5miniprogram']
        ]);
        if (!$validate->check($data)) {
            return ReturnData::create(ReturnData::FAIL, null, $validate->getError());
        }

        //获取订单商品列表
        $order_goods = logic('Cart')->cartCheckoutGoodsList(array('cartids' => $data['cartids'], 'user_id' => $data['user_id']));
        if (empty($order_goods['list'])) {
            return ReturnData::create(ReturnData::SYSTEM_FAIL, null, '订单商品不存在');
        }

        //获取收货地址
        $user_address = model('UserAddress')->getOne(array('user_id' => $data['user_id'], 'id' => $data['user_address_id']));
        if (!$user_address) {
            return ReturnData::create(ReturnData::SYSTEM_FAIL, null, '收货地址不存在');
        }

        $discount = 0; //优惠金额 = 优惠券

        //获取优惠券信息
        if ($data['user_bonus_id'] > 0) {
            $where_user_bonus['id'] = $data['user_bonus_id']; //优惠券ID
            $where_user_bonus['user_id'] = $data['user_id']; //用户ID
            $where_user_bonus['min_amount'] = $order_goods['total_price']; //商品总金额
            $user_bonus = logic('UserBonus')->getUserAvailableBonus($where_user_bonus);
            if ($user_bonus) {
                $discount = $discount + $user_bonus['bonus_money'];
            }
        }

        $order_amount = $order_goods['total_price'] - $discount;
        $pay_status = 0; //未付款

        //如果各种优惠金额大于订单实际金额跟运费之和，则默认订单状态为已付款
        if ($order_amount < 0) {
            $order_amount = 0;
            $pay_status = 1; //已付款
        }

        $time = time();
        //构造订单字段
        $order_info = array(
            'order_sn' => date('YmdHis') . rand(1000, 9999),
            'add_time' => $time,
            'update_time' => $time,
            'pay_status' => $pay_status,
            'user_id' => $data['user_id'],
            'goods_amount' => $order_goods['total_price'], //商品的总金额
            'order_amount' => $order_amount, //应付金额=商品总价+运费-优惠(积分、红包)
            'discount' => $discount, //优惠金额
            'name' => $user_address['name'],
            'country_id' => $user_address['country_id'],
            'province_id' => $user_address['province_id'],
            'city_id' => $user_address['city_id'],
            'district_id' => $user_address['district_id'],
            'address' => $user_address['address'],
            'zipcode' => $user_address['zipcode'],
            'mobile' => $user_address['mobile'],
            'place_type' => $data['place_type'], //订单来源
            'bonus_id' => isset($user_bonus['id']) ? $user_bonus['id'] : 0,
            'bonus_money' => isset($user_bonus['bonus_money']) ? $user_bonus['bonus_money'] : 0,
            'message' => $data['message'] ? $data['message'] : '',
        );

        //插入订单
        $check = $this->getValidate()->scene('add')->check($order_info);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        //判断订单号
        if (isset($order_info['order_sn']) && $order_info['order_sn'] != '') {
            $where_sn['order_sn'] = $order_info['order_sn'];
            if ($this->getModel()->getOne($where_sn)) {
                return ReturnData::create(ReturnData::FAIL, null, '该订单号已存在');
            }
        }

        // 启动事务
        Db::startTrans();
        $order_id = $this->getModel()->add($order_info, $type);
        if (!$order_id) {
            // 回滚事务
            Db::rollback();
            return ReturnData::create(ReturnData::FAIL, null, '生成订单失败');
        }

        //订单生成成功之后，扣除用户的积分和改变优惠券的使用状态
        //改变优惠券使用状态
        if ($data['user_bonus_id'] > 0) {
            model('UserBonus')->edit(array('status' => UserBonus::USER_BONUS_STATUS_USED, 'used_time' => time()), array('user_id' => $data['user_id'], 'id' => $data['user_bonus_id']));
        }
        //扣除用户积分，预留
        //$updateMember['validscore'] = $addressInfo['validscore']-$PointPay;
        //M("Member")->where(array('id'=>$CustomerSysNo))->save($updateMember);
        //增加一条积分支出记录，一条购物获取积分记录

        //插入订单商品
        $order_goods_list = array();
        foreach ($order_goods['list'] as $k => $v) {
            $goods = model('Goods')->getOne(array('id' => $v['goods_id']));
            $temp_order_goods = array(
                'order_id' => $order_id,
                'goods_id' => $v['goods_id'],
                'goods_name' => $v['title'],
                'goods_number' => $v['goods_number'],
                'market_price' => $v['market_price'],
                'goods_price' => $v['price'],
                'goods_attr' => '', //商品属性，预留
                'goods_img' => $v['litpic']
            );
            array_push($order_goods_list, $temp_order_goods);

            if ($v['goods_number'] > $goods['goods_number']) {
                Db::rollback();
                return ReturnData::create(ReturnData::SYSTEM_FAIL, null, '商品库存不足');
            }

            //订单商品直接减库存操作
            model('Goods')->changeGoodsStock(array('goods_id' => $v['goods_id'], 'goods_number' => $v['goods_number']));
            //增加商品销量
            model('Goods')->setIncrement(array('id' => $v['goods_id']), 'sale', $v['goods_number']);
        }
        $result = model('OrderGoods')->add($order_goods_list, 2);
        if (!$result) {
            // 回滚事务
            Db::rollback();
            return ReturnData::create(ReturnData::SYSTEM_FAIL, null, '订单商品添加失败');
        }

        //删除购物对应的记录
        model('Cart')->del(array('user_id' => $data['user_id'], 'id' => array('in', explode('_', $data['cartids']))));

        // 提交事务
        Db::commit();
        return ReturnData::create(ReturnData::SUCCESS, $order_id);
    }

    //修改
    public function edit($data, $where = array())
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::SUCCESS);
        }

        //更新时间
        if (!(isset($data['update_time']) && !empty($data['update_time']))) {
            $data['update_time'] = time();
        }

        $record = $this->getModel()->getOne($where);
        if (!$record) {
            return ReturnData::create(ReturnData::RECORD_NOT_EXIST);
        }

        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    //删除
    public function del($where)
    {
        if (empty($where)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        $check = $this->getValidate()->scene('del')->check($where);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        $record = $this->getModel()->getOne($where);
        if (!$record) {
            return ReturnData::create(ReturnData::RECORD_NOT_EXIST);
        }

        if ($record['order_status'] == 3 && $record['refund_status'] == 0) {

        } elseif ($record['order_status'] == 1) {

        } elseif ($record['order_status'] == 2) {

        } else {
            return ReturnData::create(ReturnData::RECORD_NOT_EXIST);
        }

        $res = $this->getModel()->edit(array('delete_time' => time()), $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    /**
     * 数据获取器
     * @param array $data 要转化的数据
     * @return array
     */
    private function getDataView($data = array())
    {
        return getDataAttr($this->getModel(), $data);
    }

    /**
     * 用户-取消订单
     * @param int $data ['id'] 订单id
     * @param int $data ['user_id'] 用户id
     * @return array
     */
    public function userCancelOrder($where = array())
    {
        if (empty($where)) {
            return ReturnData::create(ReturnData::SUCCESS);
        }

        $where['order_status'] = 0;
        $where['pay_status'] = 0;
        $order = $this->getModel()->getOne($where);
        if (!$order) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '订单不存在');
        }

        $data['order_status'] = 1;
        $data['update_time'] = time();
        $res = $this->getModel()->edit($data, $where);
        if ($res) {
            return ReturnData::create(ReturnData::SUCCESS, $res);
        }

        return ReturnData::create(ReturnData::FAIL);
    }

    /**
     * 订单-余额支付
     * @param int $where ['id'] 订单id
     * @param int $where ['user_id'] 用户id
     * @return array
     */
    public function orderYuepay($where = array())
    {
        if (empty($where)) {
            return ReturnData::create(ReturnData::SUCCESS);
        }

        $where['order_status'] = 0;
        $where['pay_status'] = 0;
        $order = $this->getModel()->getOne($where);
        if (!$order) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '订单不存在');
        }

        //获取用户余额信息
        $user = model('User')->getOne(array('id' => $where['user_id']));
        if (!$user) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在');
        }
        if ($user['money'] < $order['order_amount']) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '余额不足');
        }

        Db::startTrans();

        $time = time();
        $data['pay_status'] = 1;
        $data['pay_money'] = $order['order_amount']; //支付金额
        $data['payment_id'] = 1;
        $data['pay_name'] = model('Payment')->getValue(array('id' => $data['payment_id']), 'pay_name');
        $data['pay_time'] = $time;
        $data['update_time'] = $time;
        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            Db::rollback();
            return ReturnData::create(ReturnData::FAIL);
        }

        //添加用户余额明细
        $user_money_data['user_id'] = $where['user_id'];
        $user_money_data['type'] = 1;
        $user_money_data['money'] = $order['order_amount'];
        $user_money_data['desc'] = '订单余额支付';
        $user_money = logic('UserMoney')->add($user_money_data);
        if ($user_money['code'] != ReturnData::SUCCESS) {
            Db::rollback();
            return ReturnData::create(ReturnData::FAIL, null, $user_money['msg']);
        }

        Db::commit();
        return ReturnData::create(ReturnData::SUCCESS, $res, '支付成功');
    }

    /**
     * 订单-确认收货
     * @param int $where ['id'] 订单id
     * @param int $where ['user_id'] 用户id
     * @return array
     */
    public function orderReceiptConfirm($where = array())
    {
        if (empty($where)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        //判断订单是否存在或本人
        $where['order_status'] = 0;
        $where['refund_status'] = 0;
        $where['pay_status'] = 1;
        $order = $this->getModel()->getOne($where);
        if (!$order) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '订单不存在');
        }

        $data['order_status'] = 3;
        $data['shipping_status'] = 2;
        $data['refund_status'] = 0;
        $data['is_comment'] = 0;
        $data['update_time'] = time();
        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS);
    }

    /**
     * 订单-退款退货
     * @param int $where ['id'] 订单id
     * @param int $where ['user_id'] 用户id
     * @return array
     */
    public function orderRefund($where = array())
    {
        if (empty($where)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        $where['order_status'] = 3;
        $where['refund_status'] = 0;
        $order = $this->getModel()->getOne($where);
        if (!$order) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '订单不存在');
        }

        $data['refund_status'] = 1;
        $data['update_time'] = time();
        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS);
    }

    /**
     * 订单-设为评价
     * @param int $where ['id'] 订单id
     * @param int $where ['user_id'] 用户id
     * @return array
     */
    public function orderSetComment($where = array())
    {
        if (empty($where)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        $where['order_status'] = 3;
        $where['refund_status'] = 0;
        $data['is_comment'] = Order::ORDER_UN_COMMENT;
        $order = $this->getModel()->getOne($where);
        if (!$order) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '订单不存在或已评价');
        }

        $data['is_comment'] = Order::ORDER_IS_COMMENT;
        $data['update_time'] = time();
        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS);
    }

}