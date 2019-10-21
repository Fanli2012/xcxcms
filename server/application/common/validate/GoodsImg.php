<?php

namespace app\common\validate;

use think\Validate;

class GoodsImg extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID必填|ID必须是数字|ID格式不正确'],
        ['goods_id', 'require|number|gt:0', '商品ID必填|商品ID必须是数字|商品ID格式不正确'],
        ['url', 'require|max:150', '图片地址必填|图片地址不能超过150个字符'],
        ['des', 'max:150', '描述不能超过150个字符'],
        ['listorder', 'number|egt:0', '排序必须是数字|排序格式不正确'],
        ['add_time', 'require|number|egt:0', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['goods_id', 'url', 'des', 'listorder', 'add_time'],
        'edit' => ['goods_id', 'url', 'des', 'listorder', 'add_time'],
        'del' => ['id'],
    ];
}