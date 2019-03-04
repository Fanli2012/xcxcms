<?php
namespace app\common\validate;
use think\Validate;

class AdminRole extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number','ID必填|ID必须是数字'],
        ['parent_id', 'number|egt:0','父级ID必须是数字|父级ID格式不正确'],
        ['name', 'require|max:30','名称必填|名称不能超过30个字符'],
        ['des', 'max:150','描述不能超过150个字符'],
        ['listorder', 'number|egt:0','排序必须是数字|排序格式不正确'],
        ['status', 'in:0,1,2','状态，0正常，1禁用'],
    ];
    
    protected $scene = [
        'add'  => ['parent_id', 'name', 'des', 'listorder', 'status'],
        'edit' => ['parent_id', 'name', 'des', 'listorder', 'status'],
        'del'  => ['id'],
    ];
}