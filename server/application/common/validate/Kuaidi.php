<?php

namespace app\common\validate;

use think\Validate;

class Kuaidi extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID必填|ID必须是数字|ID格式不正确'],
        ['name', 'require|max:30', '快递公司名称必填|快递公司名称不能超过30个字符'],
        ['code', 'require|max:20', '公司编码必填|公司编码不能超过20个字符'],
        ['money', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '快递费必填|快递费只能带2位小数的数字'],
        ['country', 'max:20', '国家编码不能超过20个字符'],
        ['desc', 'max:150', '说明不能超过150个字符'],
        ['tel', 'max:60', '电话不能超过60个字符'],
        ['website', 'max:60', '官网不能超过60个字符'],
        ['listorder', 'number|max:11', '排序必须是数字|排序格式不正确'],
        ['status', 'in:0,1', '是否显示，0显示'],
    ];

    protected $scene = [
        'add' => ['name', 'code', 'money', 'country', 'desc', 'tel', 'website', 'listorder', 'status'],
        'edit' => ['name', 'code', 'money', 'country', 'desc', 'tel', 'website', 'listorder', 'status'],
        'del' => ['id'],
    ];
}