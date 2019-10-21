<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class UserRank extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['title', 'require|max:30', '会员等级名称必填|会员等级名称不能超过30个字符'],
        ['min_points', 'require|number|max:11', '该等级的最低积分必填|该等级的最低积分必须是数字|该等级的最低积分格式不正确'],
        ['max_points', 'require|number|max:11|>:min_points', '该等级的最高积分必填|该等级的最高积分必须是数字|该等级的最高积分格式不正确|最高积分应大于最低积分'],
        ['discount', 'require|number|between:0,100', '该会员等级的商品折扣必填|该会员等级的商品折扣格式不正确|该会员等级的商品折扣格式不正确'],
        ['rank', 'require|number|>:0|max:11', '会员等级必填|会员等级格式不正确|会员等级要大于0|会员等级格式不正确'],
        ['listorder', 'number|max:11', '排序格式不正确|排序格式不正确'],
    ];

    protected $scene = [
        'add' => ['title', 'min_points', 'max_points', 'discount', 'rank', 'listorder'],
        'edit' => ['title', 'min_points', 'max_points', 'discount', 'rank', 'listorder'],
        'del' => ['user_id'],
    ];
}