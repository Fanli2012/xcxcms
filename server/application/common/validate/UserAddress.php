<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class UserAddress extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['user_id', 'require|number|max:11', '用户ID必填|用户ID必须是数字|用户ID格式不正确'],
        ['name', 'require|max:60', '收货人姓名必填|收货人姓名格式不正确'],
        ['country_id', 'require|number|max:11', '收货人的国家ID必填|收货人的国家ID必须是数字|收货人的国家ID格式不正确'],
        ['province_id', 'require|number|max:11', '收货人的省份ID必填|收货人的省份ID必须是数字|收货人的省份ID格式不正确'],
        ['city_id', 'require|number|max:11', '收货人城市ID必填|收货人城市ID必须是数字|收货人城市ID格式不正确'],
        ['district_id', 'require|number|max:11', '收货人的地区ID必填|收货人的地区ID必须是数字|收货人的地区ID格式不正确'],
        ['address', 'require|max:60', '收货人的详细地址必填|收货人的详细地址格式不正确'],
        ['mobile', 'require|isMobile', '收货人的手机号必填|收货人的手机号格式不正确'],
        ['telphone', 'max:30', '收货人的电话格式不正确'],
        ['zipcode', 'max:30', '收货人的邮编格式不正确'],
        ['is_default', 'in:0,1', '是否默认,0:为非默认,1:默认'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['user_id', 'name', 'country_id', 'province_id', 'city_id', 'district_id', 'address', 'mobile', 'telphone', 'zipcode', 'is_default'],
        'edit' => ['name', 'country_id', 'province_id', 'city_id', 'district_id', 'address', 'mobile', 'telphone', 'zipcode', 'is_default'],
        'del' => ['id', 'user_id'],
    ];

    // 手机号校验
    protected function isMobile($value, $rule, $data)
    {
        if (empty($value)) {
            return '手机号不能为空';
        }

        if (Validator::isMobile($value)) {
            return true;
        }

        return '手机号格式不正确';
    }
}