<?php

namespace app\common\validate;

use think\Validate;

class GoodsBrand extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID必填|ID必须是数字|ID格式不正确'],
        ['parent_id', 'number|egt:0', '父级ID必须是数字|父级ID格式不正确'],
        ['name', 'require|max:30', '品牌名称必填|品牌名称不能超过30个字符'],
        ['seotitle', 'max:150', 'seo标题不能超过150个字符'],
        ['keywords', 'max:60', '关键词不能超过60个字符'],
        ['description', 'max:250', '描述不能超过250个字符'],
        ['click', 'number|egt:0', '点击量必须是数字|点击量格式不正确'],
        ['litpic', 'max:150', '缩略图不能超过150个字符'],
        ['cover_img', 'max:150', '封面不能超过150个字符'],
        ['listorder', 'number|egt:0', '排序必须是数字|排序格式不正确'],
        ['status', 'in:0,1,2', '状态，0正常，1禁用'],
        ['add_time', 'require|number|egt:0', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['parent_id', 'name', 'seotitle', 'keywords', 'description', 'click', 'litpic', 'cover_img', 'listorder', 'status', 'add_time'],
        'edit' => ['parent_id', 'name', 'seotitle', 'keywords', 'description', 'click', 'litpic', 'cover_img', 'listorder', 'status', 'add_time'],
        'del' => ['id'],
    ];
}