<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class UserMoney extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['user_id', 'require|number|max:11', '用户ID必填|用户ID必须是数字|用户ID格式不正确'],
        ['money', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '金额必填|金额格式不正确'],
        ['desc', 'require|max:100', '描述必填|描述格式不正确'],
        ['user_money', 'regex:/^\d{0,10}(\.\d{0,2})?$/', '余额格式不正确'],
        ['type', 'require|in:0,1', '类型必填|类型：0增加,1减少'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['user_id', 'money', 'desc', 'type'],
        'edit' => ['user_id', 'money', 'desc', 'type'],
        'del' => ['user_id'],
    ];
}