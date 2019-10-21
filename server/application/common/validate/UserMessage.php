<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class UserMessage extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['goods_id', 'require|number|max:11', '商品ID必填|商品ID必须是数字|商品ID格式不正确'],
        ['user_id', 'require|number|max:11', '用户ID必填|用户ID必须是数字|用户ID格式不正确'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
        ['update_time', 'require|number|max:11', '更新时间必填|更新时间格式不正确|更新时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['goods_id', 'user_id', 'update_time', 'add_time'],
        'edit' => ['goods_id', 'user_id', 'update_time'],
        'del' => ['user_id'],
    ];
}