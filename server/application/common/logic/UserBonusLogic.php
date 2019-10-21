<?php

namespace app\common\logic;

use think\Loader;
use app\common\lib\ReturnData;
use app\common\model\UserBonus;

class UserBonusLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new UserBonus();
    }

    public function getValidate()
    {
        return Loader::validate('UserBonus');
    }

    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = '', $limit = '')
    {
        $where2 = $where;
        $where2['end_time'] = array('<', time()); //有效期
        //设置用户优惠券已过期
        $this->getModel()->edit(array('status' => 2), $where2);

        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);
        if ($res['count'] > 0) {
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
        $res = $this->getModel()->getAll($where, $order, $field, $limit);

        /* if($res)
        {
            foreach($res as $k=>$v)
            {
                //$res[$k] = $this->getDataView($v);
            }
        } */

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

        //添加时间
        if (!(isset($data['get_time']) && !empty($data['get_time']))) {
            $data['get_time'] = time();
        }

        $check = $this->getValidate()->scene('add')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }
        //通过优惠券ID获取优惠券详情
        $bonus = model('Bonus')->getOne(array('id' => $data['bonus_id']));
        if (!$bonus) {
            return ReturnData::create(ReturnData::RECORD_NOT_EXIST, null, '您来晚了，已被抢光啦');
        }
        if ($bonus['num'] == -1 || $bonus['num'] > 0) {
        } else {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '您来晚了，已被抢光啦');
        }

        if ($this->getModel()->getOne(['bonus_id' => $data['bonus_id'], 'user_id' => $data['user_id']])) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '亲，您已领取');
        }

        $data['bonus_name'] = $bonus['name'];
        $data['bonus_money'] = $bonus['money'];
        $data['min_amount'] = $bonus['min_amount'];
        $data['start_time'] = $bonus['start_time'];
        $data['end_time'] = $bonus['end_time'];

        $res = $this->getModel()->add($data, $type);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        if ($bonus['num'] > 0) {
            model('Bonus')->getDb()->where(array('id' => $data['bonus_id']))->setDec('num', 1);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res, '领取成功');
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

    /**
     * 商品结算时，获取优惠券列表
     * @param int $data ['user_id'] 用户ID
     * @param float $data ['min_amount'] 最小金额可以用的优惠券
     * @return array
     */
    public function getAvailableBonusList($data)
    {
        $where['user_id'] = $data['user_id'];
        $where['status'] = UserBonus::USER_BONUS_STATUS_UNUSED;
        $where['end_time'] = array('>=', time()); //有效期

        //满多少使用
        if (isset($data['min_amount'])) {
            $where['min_amount'] = array('<=', $data['min_amount']);
            $where['bonus_money'] = array('<=', $data['min_amount']);
        }

        $res = $this->getModel()->getAll($where, 'bonus_money desc');
        return $res;
    }

    /**
     * 获取可用优惠券
     * @param int $data ['id'] 优惠券ID
     * @param int $data ['user_id'] 用户ID
     * @param float $data ['min_amount'] 最小金额可以用的优惠券
     * @return array
     */
    public function getUserAvailableBonus(array $data)
    {
        $where['status'] = UserBonus::USER_BONUS_STATUS_UNUSED;
        $where['end_time'] = array('>=', time()); //有效期

        //满多少使用
        if (isset($data['min_amount'])) {
            $where['min_amount'] = array('<=', $data['min_amount']);
            $where['bonus_money'] = array('<=', $data['min_amount']);
        }

        $res = $this->getModel()->getOne($where);
        return $res;
    }
}