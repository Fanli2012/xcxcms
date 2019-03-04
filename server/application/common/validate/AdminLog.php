<?php
namespace app\common\validate;
use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class AdminLog extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0','ID必填|ID必须是数字|ID格式不正确'],
        ['ip', 'require|max:15|ip','IP必填|IP不能超过15个字符|IP格式不正确'],
        ['content', 'max:250','操作内容不能超过250个字符'],
        ['admin_name', 'require|max:30','名称必填|名称不能超过30个字符'],
        ['admin_id', 'require|number|gt:0','管理员ID必填|管理员ID必须是数字|管理员ID格式不正确'],
        ['route', 'require|max:60','路由必填|路由不能超过60个字符'],
        ['http_method', 'require|max:10','请求方式必填|请求方式不能超过10个字符'],
        ['add_time', 'require|number|egt:0', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];
    
    protected $scene = [
        'add'  => ['ip', 'content', 'admin_name', 'admin_id', 'route', 'http_method', 'add_time'],
        'edit' => ['ip', 'content', 'admin_name', 'admin_id', 'route', 'http_method', 'add_time'],
        'del'  => ['id'],
    ];
}