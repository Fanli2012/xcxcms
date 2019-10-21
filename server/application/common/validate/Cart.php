<?php

namespace app\common\validate;

use think\Validate;

class Cart extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['user_id', 'require|number|max:11', '用户ID必填|用户ID必须是数字|用户ID格式不正确'],
        ['goods_id', 'require|number|max:11', '商品ID必填|商品ID必须是数字|商品ID格式不正确'],
        ['shop_id', 'number|max:11', '店铺ID必须是数字|店铺ID格式不正确'],
        ['goods_number', 'require|number|max:11', '商品数量必填|商品数量必须是数字|商品数量格式不正确'],
        ['type', 'in:0,1,2,3,4', '购物车商品类型;0普通;1团够;2拍卖;3夺宝奇兵'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['user_id', 'goods_id', 'shop_id', 'goods_number', 'type'],
        'edit' => ['user_id', 'goods_id', 'shop_id', 'goods_number', 'type'],
        'del' => ['id', 'user_id'],
    ];
}