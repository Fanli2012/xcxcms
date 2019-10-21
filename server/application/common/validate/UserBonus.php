<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class UserBonus extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['user_id', 'require|number|max:11', '用户ID必填|用户ID必须是数字|用户ID格式不正确'],
        ['bonus_id', 'require|number|max:11', '优惠券ID必填|优惠券ID必须是数字|优惠券ID格式不正确'],
        ['status', 'in:0,1,2', '状态：0未使用1已使用2已过期'],
        ['use_time', 'number|max:11', '使用时间格式不正确|使用时间格式不正确'],
        ['get_time', 'require|number|max:11', '获取时间必填|获取时间格式不正确|获取时间格式不正确'],
        ['bonus_name', 'require|max:60', '优惠券名称不能超过60个字符'],
        ['bonus_money', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '优惠券金额必填|优惠券金额格式不正确'],
        ['min_amount', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '满多少使用必填|满多少使用格式不正确'],
        ['start_time', 'require|number|max:11|<:end_time', '开始时间必填|开始时间格式不正确|开始时间格式不正确|开始时间必须小于结束时间'],
        ['end_time', 'require|number|max:11|>:start_time', '结束时间必填|结束时间格式不正确|结束时间格式不正确|开始时间必须小于结束时间'],
    ];

    protected $scene = [
        'add' => ['user_id', 'bonus_id'],
        'edit' => ['user_id', 'bonus_id', 'get_time', 'bonus_name', 'bonus_money', 'min_amount', 'start_time', 'end_time'],
        'del' => ['id'],
    ];
}