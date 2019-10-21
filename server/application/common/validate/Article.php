<?php

namespace app\common\validate;

use think\Validate;

class Article extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['type_id', 'require|number|max:11', '栏目ID必填|栏目ID必须是数字|栏目ID格式不正确'],
        ['tuijian', 'number|egt:0', '推荐等级必须是数字|推荐等级格式不正确'],
        ['click', 'number|egt:0', '点击量必须是数字|点击量格式不正确'],
        ['title', 'require|max:150', '标题必填|标题不能超过150个字符'],
        ['writer', 'max:20', '作者不能超过20个字符'],
        ['source', 'max:30', '来源不能超过30个字符'],
        ['litpic', 'max:150', '缩略图不能超过150个字符'],
        ['keywords', 'max:60', '关键词不能超过60个字符'],
        ['seotitle', 'max:150', 'seo标题不能超过150个字符'],
        ['description', 'max:250', '描述不能超过250个字符'],
        ['status', 'in:0,1', '审核状态：0正常，1未审核'],
        ['type_id2', 'number|gt:0', '栏目ID必须是数字|栏目ID格式不正确'],
        ['user_id', 'number|max:11', '发布者ID必须是数字|发布者ID格式不正确'],
        ['shop_id', 'number|max:11', '店铺ID必须是数字|店铺ID格式不正确'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
        ['update_time', 'require|number|max:11', '更新时间必填|更新时间格式不正确|更新时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['type_id', 'tuijian', 'click', 'title', 'writer', 'source', 'litpic', 'keywords', 'seotitle', 'description', 'status', 'type_id2', 'user_id', 'shop_id', 'add_time', 'update_time'],
        'edit' => ['type_id', 'tuijian', 'click', 'title', 'writer', 'source', 'litpic', 'keywords', 'seotitle', 'description', 'status', 'type_id2', 'user_id', 'shop_id', 'add_time', 'update_time'],
        'del' => ['id'],
    ];
}