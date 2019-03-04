<?php
namespace app\common\validate;
use think\Validate;

class Menu extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number','ID必填|ID必须是数字'],
        ['parent_id', 'number|egt:0', '父级ID必须是数字|父级ID格式不正确'],
        ['module', 'require|alphaDash|max:50','模型必填|模型格式不正确|模型不能超过50个字符'],
        ['controller', 'require|alphaDash|max:50','控制器必填|控制器格式不正确|控制器不能超过50个字符'],
        ['action', 'require|alphaDash|max:50','方法必填|方法格式不正确|方法不能超过50个字符'],
        ['data', 'max:50','额外参数不能超过50个字符'],
        ['type', 'number|in:0,1','菜单类型必须是数字|菜单类型，1：权限认证+菜单；0：只作为菜单'],
        ['name', 'require|max:50','名称必填|名称不能超过50个字符'],
        ['icon', 'max:50','菜单图标不能超过50个字符'],
        ['des', 'max:250','备注不能超过250个字符'],
        ['listorder', 'number|egt:0','排序必须是数字|排序格式不正确'],
        ['status', 'in:0,1','状态，1显示，0不显示'],
    ];
    
    protected $scene = [
        'add'  => ['parent_id', 'module', 'controller', 'action', 'data', 'type', 'name', 'icon', 'des', 'listorder', 'status'],
        'edit' => ['parent_id', 'module', 'controller', 'action', 'data', 'type', 'name', 'icon', 'des', 'listorder', 'status'],
        'del'  => ['id'],
    ];
}