<?php

namespace app\common\logic;

use think\Loader;
use app\common\lib\ReturnData;
use app\common\model\Cart;
use app\common\model\Goods;

class CartLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new Cart();
    }

    public function getValidate()
    {
        return Loader::validate('Cart');
    }

    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = '', $limit = '')
    {
        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {
                //$res['list'][$k] = $this->getDataView($v);
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
            return $item;
        });

        return $res;
    }

    //全部列表
    public function getAll($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getDb()->alias('c')->join(config('database.prefix') . 'goods g', 'g.id = c.goods_id')
            ->where(['g.status' => Goods::GOODS_STATUS_NORMAL, 'c.user_id' => $where['user_id']])
            ->field('c.*,g.id as goods_id,g.title,g.sn,g.price as goods_price,g.market_price,g.litpic,g.goods_number as stock,g.promote_start_date,g.promote_price,g.promote_end_date')
            ->select();

        if (!$res) {
            return $res;
        }

        foreach ($res as $k => $v) {
            $res[$k]['is_promote'] = 0;
            if (model('Goods')->bargain_price($v['goods_price'], $v['promote_start_date'], $v['promote_end_date']) > 0) {
                $res[$k]['is_promote'] = 1;
            }

            $goods_tmp = array('price' => $v['goods_price'], 'promote_price' => $v['promote_price'], 'promote_start_date' => $v['promote_start_date'], 'promote_end_date' => $v['promote_end_date']);
            $res[$k]['price'] = model('Goods')->getGoodsFinalPrice($goods_tmp); //商品最终价格
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

        return $res;
    }

    //添加
    public function add($data = array(), $type = 0)
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }
        $cart_id = 0;

        //添加时间
        if (!(isset($data['add_time']) && !empty($data['add_time']))) {
            $data['add_time'] = time();
        }

        $check = $this->getValidate()->scene('add')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        //获取商品信息
        $goods = model('Goods')->getOne(array('id' => $data['goods_id'], 'status' => Goods::GOODS_STATUS_NORMAL));
        if (!$goods) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '商品不存在');
        }

        //判断库存 是否足够
        if ($goods['goods_number'] < $data['goods_number']) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '库存不足');
        }

        //判断购物车商品数
        if ($this->getModel()->getCount(array('user_id' => $data['user_id'])) >= 20) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '购物车商品最多20件');
        }

        //查看是否已经有购物车插入记录，如果没有就添加购物车，有就更新购物车
        $where = array(
            'user_id' => $data['user_id'],
            'goods_id' => $data['goods_id']
        );

        $cart = $this->getModel()->getOne($where);
        if ($cart) {
            //更新购物车
            $updateArr = array(
                'goods_number' => $data['goods_number'],
                'add_time' => time(),
            );

            $this->getModel()->edit($updateArr, array('id' => $cart['id']));
            $cart_id = $cart['id'];
        } else {
            //添加购物车
            $cartInsert = array(
                'user_id' => $data['user_id'],
                'goods_id' => $data['goods_id'],
                'goods_number' => $data['goods_number'],
                'add_time' => time(),
            );

            $cart_id = $this->getModel()->add($cartInsert, $type);
        }

        if ($cart_id) {
            return ReturnData::create(ReturnData::SUCCESS, $cart_id, '购物车添加成功');
        }
        return ReturnData::create(ReturnData::FAIL);
    }

    //修改
    public function edit($data, $where = array())
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::SUCCESS);
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

        $res = $this->getModel()->del($where);
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

    //购物车结算商品列表
    public function cartCheckoutGoodsList($where)
    {
        $cartIds = explode('_', $where['cartids']);

        // 获取购物车列表
        $cartList = $this->getModel()->getAll(array('user_id' => $where['user_id'], 'id' => array('in', $cartIds)));
        $total_price = 0; //商品总金额
        $total_goods = 0; //商品总数量

        if ($cartList) {
            $resultList = array();
            $checkArr = array();

            foreach ($cartList as $k => $v) {
                $goods = logic('Goods')->getOne(array('id' => $v['goods_id']));

                $cartList[$k]['is_promote'] = $goods['is_promote'];
                $cartList[$k]['price'] = $goods['price']; //商品最终价格
                $cartList[$k]['title'] = $goods['title'];
                $cartList[$k]['litpic'] = $goods['litpic'];
                $cartList[$k]['market_price'] = $goods['market_price'];
                $cartList[$k]['goods_sn'] = $goods['sn'];

                $total_price = $total_price + $cartList[$k]['price'] * $cartList[$k]['goods_number'];
                $total_goods = $total_goods + $cartList[$k]['goods_number'];
            }
        }

        $res['list'] = $cartList;
        $res['total_price'] = $total_price;
        $res['total_goods'] = $total_goods;

        return $res;
    }
}