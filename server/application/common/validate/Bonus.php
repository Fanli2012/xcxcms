<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class Bonus extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['name', 'require|max:60', '优惠券名称不能超过60个字符'],
        ['money', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/|>:0', '优惠券金额必填|优惠券金额格式不正确|优惠券金额必须大于0'],
        ['min_amount', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '满多少使用必填|满多少使用格式不正确'],
        ['start_time', 'require|number|max:11', '开始时间必填|开始时间格式不正确|开始时间格式不正确'],
        ['end_time', 'require|number|max:11|>:start_time', '结束时间必填|结束时间格式不正确|结束时间格式不正确|开始时间必须小于结束时间'],
        ['num', 'number|max:11', '优惠券数量必须是数字|优惠券数量格式不正确'],
        ['point', 'number|max:11', '兑换优惠券所需积分必填|兑换优惠券所需积分必须是数字'],
        ['status', 'in:0,1', '状态：0可用，1不可用'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['name', 'money', 'min_amount', 'start_time', 'end_time', 'num', 'point', 'status', 'add_time'],
        'edit' => ['name', 'money', 'min_amount', 'start_time', 'end_time', 'num', 'point', 'status'],
        'del' => ['id'],
    ];
}