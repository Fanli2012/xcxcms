<?php
namespace app\common\validate;
use think\Validate;

class Tag extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0','ID必填|ID必须是数字|ID格式不正确'],
        ['name', 'require|max:36','名称必填|名称不能超过36个字符'],
        ['title', 'max:60','SEO标题不能超过60个字符'],
        ['keywords', 'max:100','关键词不能超过100个字符'],
        ['description', 'max:250','描述不能超过250个字符'],
        ['click', 'number|egt:0', '点击量必须是数字|点击量格式不正确'],
        ['litpic', 'max:150','缩略图不能超过150个字符'],
        ['template', 'max:30','模板名称不能超过30个字符'],
        ['filename', 'max:60|regex:/^[a-z]{1,}[a-z0-9]*$/','别名不能超过60个字符|别名格式不正确'],
        ['status', 'in:0,1,2','状态，0正常，1禁用'],
        ['add_time', 'require|number|egt:0', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
        ['update_time', 'require|number|egt:0', '更新时间必填|更新时间格式不正确|更新时间格式不正确'],
    ];
    
    protected $scene = [
        'add'  => ['name', 'title', 'keywords', 'description', 'click', 'litpic', 'template', 'filename', 'status', 'add_time', 'update_time'],
        'edit' => ['name', 'title', 'keywords', 'description', 'click', 'litpic', 'template', 'filename', 'status', 'update_time'],
        'del'  => ['id'],
    ];
}