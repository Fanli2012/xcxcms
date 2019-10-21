<?php

namespace app\common\logic;

use think\Loader;
use think\Db;
use app\common\lib\ReturnData;
use app\common\model\UserReferralCommission;

class UserReferralCommissionLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new UserReferralCommission();
    }

    public function getValidate()
    {
        return Loader::validate('UserReferralCommission');
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

        //添加时间、更新时间
        $time = time();
        if (!(isset($data['add_time']) && !empty($data['add_time']))) {
            $data['add_time'] = $time;
        }
        if (!(isset($data['update_time']) && !empty($data['update_time']))) {
            $data['update_time'] = $time;
        }

        $check = $this->getValidate()->scene('add')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        $res = $this->getModel()->add($data, $type);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
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

        $check = $this->getValidate()->scene('edit')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
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
     * 佣金转换为帐户余额
     * @param int $user_id 用户ID
     * @param float $money 金额
     * @return array
     */
    public function userReferralCommissionTurnUserMoney($user_id, $money)
    {
        if ($money <= 0) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '金额必须大于零');
        }

        $user = model('User')->getOne(['id' => $user_id]);
        if (!$user) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在');
        }

        $user_referral_commission = model('UserReferralCommission')->getOne(['user_id' => $user_id]);
        if (!$user_referral_commission) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户推介资金不存在');
        }
        //判断可提取佣金是否足够
        if ($money > $user_referral_commission['commission_available']) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '可提取佣金不足');
        }
        Db::startTrans(); //启动事务
        //修改用户推介资金
        $user_referral_commission_edit['commission_withdraw'] = $user_referral_commission['commission_withdraw'] + $money;
        $res = model('UserReferralCommission')->edit($user_referral_commission_edit, array('user_id' => $user_id));
        if (!$res) {
            Db::rollback(); //事务回滚
            return ReturnData::create(ReturnData::FAIL);
        }
        //添加用户推介资金明细
        $user_referral_commission_add['user_id'] = $user_id;
        $user_referral_commission_add['type'] = 1;
        $user_referral_commission_add['money'] = $money;
        $user_referral_commission_add['desc'] = '佣金转换为帐户余额';
        $res = logic('UserReferralCommissionLog')->add($user_referral_commission_add);
        if ($res['code'] != ReturnData::SUCCESS) {
            Db::rollback(); //事务回滚
            return ReturnData::create(ReturnData::FAIL);
        }
        //增加用户余额
        $user_money_add['user_id'] = $user_id;
        $user_money_add['type'] = 0;
        $user_money_add['money'] = $money;
        $user_money_add['desc'] = '佣金转换为帐户余额';
        $res = logic('UserMoney')->add($user_money_add);
        if ($res['code'] != ReturnData::SUCCESS) {
            Db::rollback(); //事务回滚
            return ReturnData::create(ReturnData::FAIL);
        }

        Db::commit(); //事务提交
        return ReturnData::create(ReturnData::SUCCESS);
    }
}