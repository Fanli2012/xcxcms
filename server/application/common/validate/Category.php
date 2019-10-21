<?php

namespace app\common\validate;

use think\Validate;

class Category extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number', 'ID必填|ID必须是数字'],
        ['parent_id', 'number', '父级id必须是数字'],
        ['add_time', 'number', '添加时间必须是数字'],
        ['name', 'require|max:30', '类目名称必填|类目名称不能超过30个字符'],
        ['seotitle', 'max:150', 'seo标题不能超过150个字符'],
        ['keywords', 'max:60', '关键词不能超过60个字符'],
        ['description', 'max:250', '描述不能超过250个字符'],
        ['listorder', 'number', '排序必须是数字'],
        ['litpic', 'max:100', '封面或缩略图不能超过100个字符'],
    ];

    protected $scene = [
        'add' => ['parent_id', 'name', 'seotitle', 'keywords', 'description', 'listorder', 'litpic'],
        'edit' => ['id', 'parent_id', 'name', 'seotitle', 'keywords', 'description', 'listorder', 'litpic'],
        'del' => ['id'],
    ];

    /**
     * 类目名称验证
     * 参数依次为验证数据，验证规则，全部数据(数组)，字段名
     */
    protected function checkCategoryNameUnique($value, $rule, $data, $field)
    {
        $res = model('Category')->getOne(['name' => $value, 'id' => ['<>', $data['id']]]);
        if ($res) {
            return '该类目名称已经存在';
        }

        return true;
    }
}