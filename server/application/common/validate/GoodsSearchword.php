<?php
namespace app\common\validate;
use think\Validate;

class GoodsSearchword extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0','ID必填|ID必须是数字|ID格式不正确'],
        ['name', 'require|max:60','名称必填|名称不能超过60个字符'],
        ['click', 'number|egt:0', '点击量必须是数字|点击量格式不正确'],
        ['listorder', 'number|egt:0','排序必须是数字|排序格式不正确'],
        ['status', 'in:0,1','状态，0正常，1禁用'],
        ['add_time', 'require|number|egt:0', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
        ['update_time', 'require|number|egt:0', '更新时间必填|更新时间格式不正确|更新时间格式不正确'],
    ];
    
    protected $scene = [
        'add'  => ['name', 'click', 'listorder', 'status', 'add_time', 'update_time'],
        'edit' => ['name', 'click', 'listorder', 'status', 'update_time'],
        'del'  => ['id'],
    ];
}