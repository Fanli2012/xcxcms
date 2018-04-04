<?php
namespace app\common\validate;

use think\Validate;

class Article extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number','ID必填|ID必须是数字'],
        ['typeid', 'require|number','栏目ID必填|栏目ID必须是数字'],
        ['tuijian', 'number', '推荐等级必须是数字'],
        ['click', 'number', '点击量必须是数字'],
        ['title', 'require|max:150','标题必填|标题不能超过150个字符'],
        ['writer', 'max:20','作者不能超过20个字符'],
        ['source', 'max:30','来源不能超过30个字符'],
        ['litpic', 'max:100','缩略图不能超过100个字符'],
        ['pubdate', 'number', '更新时间格式不正确'],
        ['addtime', 'require|number', '添加时间必填|添加时间必须是数字'],
        ['keywords', 'max:60','关键词不能超过60个字符'],
        ['seotitle', 'max:150','seo标题不能超过150个字符'],
        ['description', 'max:250','描述不能超过250个字符'],
        ['ischeck', 'in:0,1','审核状态：0审核，1未审核'],
        ['user_id', 'number', '发布者ID必须是数字'],
    ];
    
    protected $scene = [
        'add' => ['typeid', 'title', 'tuijian', 'click', 'writer', 'source', 'litpic', 'pubdate', 'addtime', 'keywords', 'seotitle', 'description', 'ischeck', 'user_id'],
        'del' => ['id'],
    ];
}