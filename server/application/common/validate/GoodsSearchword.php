<?php

namespace app\common\validate;

use think\Validate;

class GoodsSearchword extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['name', 'require|max:30', '搜索词必填|搜索词不能超过30个字符'],
        ['click', 'number|max:11', '点击量必须是数字|点击量格式不正确'],
        ['listorder', 'number|max:11', '排序必须是数字|排序格式不正确'],
        ['status', 'in:0,1', '状态：0正常，1禁用'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['name', 'click', 'listorder', 'status', 'add_time'],
        'edit' => ['name', 'click', 'listorder', 'status'],
        'del' => ['id'],
    ];
}