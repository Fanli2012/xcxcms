<?php

namespace app\common\validate;

use think\Validate;

class Friendlink extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID必填|ID必须是数字|ID格式不正确'],
        ['name', 'require|max:30', '链接名称必填|链接名称不能超过30个字符'],
        ['url', 'require|max:150', '跳转链接名称必填|跳转链接不能超过150个字符'],
        ['target', 'number|egt:0', '跳转方式必须是数字|跳转方式格式不正确'],
        ['group_id', 'number|egt:0', '分组ID必须是数字|分组ID格式不正确'],
        ['listorder', 'number|egt:0', '排序必须是数字|排序格式不正确'],
    ];

    protected $scene = [
        'add' => ['name', 'url', 'target', 'group_id', 'listorder'],
        'edit' => ['name', 'url', 'target', 'group_id', 'listorder'],
        'del' => ['id'],
    ];
}