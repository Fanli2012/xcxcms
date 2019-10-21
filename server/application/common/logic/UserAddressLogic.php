<?php

namespace app\common\logic;

use think\Loader;
use app\common\lib\ReturnData;
use app\common\model\UserAddress;

class UserAddressLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new UserAddress();
    }

    public function getValidate()
    {
        return Loader::validate('UserAddress');
    }

    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = '', $limit = '')
    {
        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {
                //$res['list'][$k] = $this->getDataView($v);
                $res['list'][$k] = $res['list'][$k]->append(['country_name', 'province_name', 'city_name', 'district_name', 'is_default_text'])->toArray();
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

        $res = $res->append(['country_name', 'province_name', 'city_name', 'district_name', 'is_default_text'])->toArray();
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
        if (!(isset($data['add_time']) && !empty($data['add_time']))) {
            $data['add_time'] = time();
        }

        $check = $this->getValidate()->scene('add')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        if ($this->getModel()->getCount(['user_id' => $data['user_id']]) >= 10) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '最多10个收货地址');
        }

        $res = $this->getModel()->add($data, $type);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        //如果没有默认地址，就设置为默认地址
        $user_address = model('User')->getDb()->alias('u')
            ->join(config('database.prefix') . 'user_address a', 'u.address_id = a.id')
            ->where(['u.id' => $data['user_id']])
            ->find();

        if (!$user_address || $data['is_default'] == UserAddress::USER_ADDRESS_IS_DEFAULT) {
            $this->setDefault(['id' => $res, 'user_id' => $data['user_id']]);
        }

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

        $record = $this->getModel()->getOne($where);
        if (!$record) {
            return ReturnData::create(ReturnData::RECORD_NOT_EXIST);
        }

        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        if ($record['is_default'] != UserAddress::USER_ADDRESS_IS_DEFAULT && $data['is_default'] == UserAddress::USER_ADDRESS_IS_DEFAULT) {
            $this->setDefault(['id' => $record['id'], 'user_id' => $record['user_id']]);
        } elseif ($record['is_default'] == UserAddress::USER_ADDRESS_IS_DEFAULT && $data['is_default'] != UserAddress::USER_ADDRESS_IS_DEFAULT) {
            // 没有默认地址
            $address = $this->userDefaultAddress(['user_id' => $record['user_id']]);
            if ($address) {
                $this->setDefault(['id' => $address['id'], 'user_id' => $record['user_id']]);
            }
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

        $res = $this->getModel()->del($where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        //判断该地址是否是默认地址
        $user = model('User')->getOne(['id' => $where['user_id'], 'address_id' => $where['id']]);
        if ($user) {
            $address = $this->userDefaultAddress(['user_id' => $where['user_id']]);
            if ($address) {
                $this->setDefault(['id' => $address['id'], 'user_id' => $where['user_id']]);
            }
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
     * 设为默认地址
     * @param int $where ['id'] user_address表id
     * @param int $where ['user_id'] 用户id
     * @return array
     */
    public function setDefault($where)
    {
        $this->getModel()->edit(['is_default' => 0], ['user_id' => $where['user_id']]);
        if ($this->getModel()->edit(['is_default' => UserAddress::USER_ADDRESS_IS_DEFAULT], $where)) {
            model('User')->edit(['address_id' => $where['id']], ['id' => $where['user_id']]);

            return true;
        }

        return false;
    }

    /**
     * 获取默认地址
     * @param int $where ['user_id'] 用户id
     * @return array
     */
    public function userDefaultAddress($where)
    {
        $arr = array();
        $arr = $this->getOne(array('user_id' => $where['user_id'], 'is_default' => UserAddress::USER_ADDRESS_IS_DEFAULT));

        if (!$arr) {
            $arr = $this->getOne(array('user_id' => $where['user_id']));
        }

        return $arr;
    }
}