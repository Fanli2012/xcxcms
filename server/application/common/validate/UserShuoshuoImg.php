<?php

namespace app\common\validate;

use think\Validate;

class UserShuoshuoImg extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID必填|ID必须是数字|ID格式不正确'],
        ['user_shuoshuo_id', 'require|number|max:11', '说说ID必填|说说ID必须是数字|说说ID格式不正确'],
        ['url', 'require|max:150', '图片地址必填|图片地址不能超过150个字符'],
        ['desc', 'max:150', '描述不能超过150个字符'],
        ['listorder', 'number|max:11', '排序必须是数字|排序格式不正确'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['user_shuoshuo_id', 'url', 'desc', 'listorder', 'add_time'],
        'edit' => ['user_shuoshuo_id', 'url', 'desc', 'listorder'],
        'del' => ['id'],
    ];
}