<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class UserRecharge extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['user_id', 'require|number|max:11', '用户ID必填|用户ID必须是数字|用户ID格式不正确'],
        ['recharge_sn', 'require|number|max:60', '支付订单号必填|支付订单号格式不正确|支付订单号格式不正确'],
        ['money', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/|between:1,10000', '充值金额必填|充值金额只能带2位小数的数字|充值金额1-10000元'],
        ['pay_time', 'number|max:11', '充值时间格式不正确|充值时间格式不正确'],
        ['pay_type', 'in:0,1,2,3', '充值类型：1微信，2支付宝'],
        ['pay_money', 'regex:/^\d{0,10}(\.\d{0,2})?$/', '支付金额只能带2位小数的数字'],
        ['trade_no', 'max:60', '支付流水号格式不正确'],
        ['status', 'in:0,1,2,3', '充值状态：0未处理，1已完成，2失败'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
        ['update_time', 'require|number|max:11', '更新时间必填|更新时间格式不正确|更新时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['user_id', 'recharge_sn', 'money'],
        'edit' => ['user_id', 'recharge_sn', 'money'],
        'del' => ['user_id'],
    ];
}