<?php

namespace app\common\validate;

use think\Validate;

class UserShuoshuoComment extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID必填|ID必须是数字|ID格式不正确'],
        ['parent_id', 'number|max:11', '父级ID必须是数字|父级ID格式不正确'],
        ['user_id', 'require|number|max:11', '用户ID必填|用户ID必须是数字|用户ID格式不正确'],
        ['user_shuoshuo_id', 'require|number|max:11', '说说ID必填|说说ID必须是数字|说说ID格式不正确'],
        ['desc', 'max:150', '描述不能超过150个字符'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['parent_id', 'user_id', 'user_shuoshuo_id', 'desc', 'add_time'],
        'edit' => ['parent_id', 'user_id', 'user_shuoshuo_id', 'desc'],
        'del' => ['id'],
    ];
}