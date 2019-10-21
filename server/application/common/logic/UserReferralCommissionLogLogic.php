<?php

namespace app\common\logic;

use think\Loader;
use think\Db;
use app\common\lib\ReturnData;
use app\common\model\UserReferralCommissionLog;

class UserReferralCommissionLogLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new UserReferralCommissionLog();
    }

    public function getValidate()
    {
        return Loader::validate('UserReferralCommissionLog');
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

    /**
     * 添加一条记录，并增加或减少用户推介资金，谨慎使用
     * @param int $data ['user_id'] 用户id
     * @param int $data ['type'] 0增加,1减少
     * @param float $data ['money'] 金额
     * @param string $data ['desc'] 描述
     * @return array
     */
    public function add($data = array(), $type = 0)
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        //添加时间
        if (!(isset($data['add_time']) && !empty($data['add_time']))) {
            $data['add_time'] = time();
        }

        $check = $this->getValidate()->scene('add')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        if ($data['money'] <= 0) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '金额必须大于零');
        }

        $user = model('User')->getOne(['id' => $data['user_id']]);
        if (!$user) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在');
        }

        $user_referral_commission = model('UserReferralCommission')->getOne(['user_id' => $data['user_id']]);
        if (!$user_referral_commission) {
            $user_referral_commission['user_id'] = $data['user_id'];
            $user_referral_commission['commission_total'] = 0;
            $user_referral_commission['commission_available'] = 0;
            $user_referral_commission['commission_withdraw'] = 0;
            $user_referral_commission['add_time'] = $user_referral_commission['update_time'] = time();
            if (!model('UserReferralCommission')->add($user_referral_commission)) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户推介资金不存在');
            }
        }

        Db::startTrans(); //启动事务

        if ($data['type'] == UserReferralCommissionLog::USER_REFERRAL_COMMISSION_LOG_INCREMENT) {
            //增加用户推介资金
            $user_referral_commission_edit['commission_total'] = $user_referral_commission['commission_total'] + $data['money'];
            $user_referral_commission_edit['commission_available'] = $user_referral_commission['commission_available'] + $data['money'];
            $user_referral_commission_edit['update_time'] = time();
            model('UserReferralCommission')->edit($user_referral_commission_edit, array('user_id' => $data['user_id']));
        } elseif ($data['type'] == UserReferralCommissionLog::USER_REFERRAL_COMMISSION_LOG_DECREMENT) {
            //判断用户推介资金是否足够
            if ($data['money'] > $user_referral_commission['commission_available']) {
                Db::rollback(); //事务回滚
                return ReturnData::create(ReturnData::FAIL, null, '佣金不足');
            }
            //减少用户推介资金
            $user_referral_commission_edit['commission_available'] = $user_referral_commission['commission_available'] - $data['money'];
            $user_referral_commission_edit['update_time'] = time();
            model('UserReferralCommission')->edit($user_referral_commission_edit, array('user_id' => $data['user_id']));
        } else {
            Db::rollback(); //事务回滚
            return ReturnData::create(ReturnData::FAIL);
        }

        $user_commission = model('UserReferralCommission')->getValue(array('user_id' => $data['user_id']), 'commission_available'); //用户推介资金
        $data['user_commission'] = $user_commission;
        $res = $this->getModel()->add($data, $type);
        if (!$res) {
            Db::rollback(); //事务回滚
            return ReturnData::create(ReturnData::FAIL);
        }

        Db::commit(); //事务提交
        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    //修改
    public function edit($data, $where = array())
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::SUCCESS);
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
}