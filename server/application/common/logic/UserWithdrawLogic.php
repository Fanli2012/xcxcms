<?php

namespace app\common\logic;

use think\Db;
use think\Loader;
use app\common\lib\ReturnData;
use app\common\model\UserWithdraw;
use app\common\model\UserMoney;

class UserWithdrawLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new UserWithdraw();
    }

    public function getValidate()
    {
        return Loader::validate('UserWithdraw');
    }

    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = '', $limit = '')
    {
        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {
                //$res['list'][$k] = $this->getDataView($v);
                $res['list'][$k] = $res['list'][$k]->append(['status_text'])->toArray();
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
            $item['user'] = model('User')->getOne(array('id' => $item['user_id']));
            $item = $item->append(['status_text'])->toArray();
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
        $res = $res->append(['status_text'])->toArray();

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

        if ($data['method'] == UserWithdraw::USER_WITHDRAW_METHOD_BANK) {
            if (!(isset($data['bank_name']) && !empty($data['bank_name']))) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '银行名称必填');
            }
            if (!(isset($data['bank_place']) && !empty($data['bank_place']))) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '开户行必填');
            }
        }

        if (isset($data['pay_password']) && !empty($data['pay_password'])) {
        } else {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '请输入支付密码');
        }

        $user = model('User')->getOne(array('id' => $data['user_id']));
        if (!$user) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在');
        }
        if ($user['pay_password'] == '') {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '请先设置支付密码');
        }
        if ($user['pay_password'] != logic('User')->passwordEncrypt($data['pay_password'])) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '支付密码错误');
        }
        unset($data['pay_password']);

        $min_withdraw_money = sysconfig('CMS_MIN_WITHDRAWAL_MONEY'); //最低可提现金额
        if ($user['money'] < $data['money']) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '余额不足');
        }
        if ($user['money'] < $min_withdraw_money) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户金额小于最小提现金额');
        }
        if ($data['money'] < $min_withdraw_money) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '提现金额不得小于最小提现金额');
        }

        Db::startTrans();

        $data['add_time'] = time();
        $res = $this->getModel()->add($data, $type);
        if (!$res) {
            Db::rollback();
            return ReturnData::create(ReturnData::FAIL);
        }

        //添加用户余额记录并扣除用户余额
        $user_money_data['user_id'] = $data['user_id'];
        $user_money_data['type'] = UserMoney::USER_MONEY_DECREMENT;
        $user_money_data['money'] = $data['money'];
        $user_money_data['desc'] = UserWithdraw::USER_WITHDRAW_DESC;
        $user_money = logic('UserMoney')->add($user_money_data);
        if ($user_money['code'] != ReturnData::SUCCESS) {
            Db::rollback();
            return ReturnData::create(ReturnData::FAIL, null, $user_money['msg']);
        }

        Db::commit();
        return ReturnData::create(ReturnData::SUCCESS, $res);
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
     * 取消/拒绝提现
     * @param int $where ['id'] 提现ID
     * @param int $data ['status'] status=3取消或status=4拒绝
     * @param string $data ['re_note'] 理由，选填
     * @return array
     */
    public function userWithdrawSuccessConfirm($data, $where)
    {
        if (empty($where) || empty($data)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }
        if ($data['status'] != 3 || $data['status'] != 4) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        $user_withdraw = $this->getModel()->getOne($where);
        if (!$user_withdraw) {
            return false;
        }

        Db::startTrans();

        $data['update_time'] = time();
        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            Db::rollback();
            return false;
        }

        //添加用户余额记录并增加用户余额
        $user_money_data['user_id'] = $user_withdraw['user_id'];
        $user_money_data['type'] = UserMoney::USER_MONEY_INCREMENT;
        $user_money_data['money'] = $user_withdraw['money'];
        $user_money_data['desc'] = '提现退回';
        $user_money = logic('UserMoney')->add($user_money_data);
        if ($user_money['code'] != ReturnData::SUCCESS) {
            Db::rollback();
            return false;
        }

        Db::commit();
        return true;
    }
}