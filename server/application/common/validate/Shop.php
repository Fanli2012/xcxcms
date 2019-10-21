<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class Shop extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number', 'ID必填|ID必须是数字'],
        ['email', 'require|max:20|email', '邮箱必填|邮箱不能超过20个字符|邮箱格式不正确'],
        ['user_name', 'require|max:60', '用户名必填|用户名不能超过60个字符'],
        ['password', 'require|max:20|checkPWD', '密码必填|密码不能超过20个字符'],
        ['old_password', 'require|max:20', '密码必填|密码不能超过20个字符'],
        ['re_password', 'require|max:20|confirm:password', '确认密码必填|确认密码不能超过20个字符|密码与确认密码不一致'],
        ['pay_password', 'max:20', '支付密码不能超过20个字符'],
        ['introduction', 'max:100', '简介不能超过100个字符'],
        ['mobile', 'require|max:20|checkPhone', '手机号必填|手机号不能超过60个字符'],
        ['status', 'number|in:0,1,2,3', '用户状态必须是数字|用户状态，0待审，1正常，2锁定'],
        ['openid', 'max:100', 'openid不能超过100个字符'],
        ['consumption_money', 'regex:/^\d{0,10}(\.\d{0,2})?$/', '累计消费金额只能带2位小数的数字'],
        ['annual_fee', 'regex:/^\d{0,10}(\.\d{0,2})?$/', '年费只能带2位小数的数字'],
        ['cover_img', 'max:250', '封面不能超过250个字符'],
        ['proxy_id', 'number', '代理商id必须是数字'],
        ['province_id', 'number', '省id必须是数字'],
        ['city_id', 'number', '市id必须是数字'],
        ['district_id', 'number', '区id必须是数字'],
        ['address', 'max:150', '详细地址不能超过150个字符'],
        ['point_lng', 'regex:/^\d{0,4}(\.\d{0,6})?$/', '经度格式不正确'],
        ['point_lat', 'regex:/^\d{0,4}(\.\d{0,6})?$/', '纬度格式不正确'],
        ['head_img', 'max:250', '头像不能超过250个字符'],
        ['wechat', 'max:20', '微信号不能超过20个字符'],
        ['qq', 'max:20', 'QQ不能超过20个字符'],
        ['company_name', 'max:100', '公司名称不能超过100个字符'],
        ['business_license_img', 'max:250', '营业执照不能超过250个字符'],
        ['industry_id', 'number', '行业id必须是数字'],
        ['click', 'number|egt:0', '点击量必须是数字|点击量格式不正确'],
        ['website', 'max:100', '官网不能超过100个字符'],
        ['contact', 'max:30', '联系人不能超过30个字符'],
        ['contact_information', 'max:30', '联系方式不能超过30个字符'],
        ['business_license_no', 'max:20', '营业执照号不能超过20个字符'],
        ['zipcode', 'max:10', '邮编不能超过10个字符'],
        ['fax', 'max:20', '传真不能超过20个字符'],
        ['expire_time', 'number|egt:0', '过期时间格式不正确|过期时间格式不正确'],
        ['main_product', 'max:100', '主营产品或服务不能超过100个字符'],
        ['zhiwu', 'max:20', '职务不能超过20个字符'],
        ['qrcode', 'max:150', '二维码不能超过150个字符'],
        ['category_id', 'require|number|>:0', '类目必填|类目格式不正确|类目必填'],
        ['captcha', 'require|checkCaptcha', '图形验证码必填'],
        ['smscode', 'require|checkSmsCode', '短信验证码必填'],
        ['smstype', 'require|number|egt:0', '短信验证码类型必填|短信验证码类型格式不正确|短信验证码类型格式不正确'],
        ['signature', 'max:60', '签名不能超过60个字符'],
        ['add_time', 'require|number|egt:0', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
        ['update_time', 'require|number|egt:0', '更新时间必填|更新时间格式不正确|更新时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['user_name', 'password', 'introduction', 'status', 'annual_fee', 'cover_img', 'proxy_id', 'province_id', 'city_id', 'district_id', 'address', 'point_lng', 'point_lat', 'head_img', 'company_name', 'business_license_img', 'industry_id', 'click', 'website', 'contact', 'contact_information', 'expire_time', 'signature'],
        'edit' => ['introduction', 'status', 'annual_fee', 'cover_img', 'proxy_id', 'province_id', 'city_id', 'district_id', 'address', 'point_lng', 'point_lat', 'head_img', 'company_name', 'business_license_img', 'industry_id', 'click', 'website', 'contact', 'contact_information', 'expire_time', 'signature'],
        'setting' => ['business_license_no', 'zipcode', 'fax', 'zhiwu', 'qrcode', 'category_id', 'introduction', 'proxy_id', 'province_id', 'city_id', 'district_id', 'address', 'point_lng', 'point_lat', 'head_img', 'company_name', 'business_license_img', 'industry_id', 'click', 'website', 'contact', 'contact_information', 'expire_time', 'signature'],
        'del' => ['id'],
        'mobile_reg' => ['mobile', 'password', 're_password', 'smscode', 'smstype'],
        'email_reg' => ['email', 'password', 're_password', 'smscode', 'smstype'],
        'resetpwd' => ['mobile', 'password', 're_password', 'smscode', 'smstype'],
        'change_password' => ['password', 're_password', 'old_password'],
    ];

    // 图形验证码验证
    protected function checkCaptcha($value)
    {
        if (!captcha_check($value)) {
            return '图形验证码错误';
        }

        return true;
    }

    /**
     * 手机号码验证
     * 参数依次为验证数据，验证规则，全部数据(数组)，字段名
     */
    protected function checkPhone($value, $rule, $data, $field)
    {
        if (Helper::isValidMobile($value)) {
            return true;
        }

        return '手机号码格式不正确';
    }

    /**
     * 邮箱验证
     * 参数依次为验证数据，验证规则，全部数据(数组)，字段名
     */
    protected function checkEmail($value, $rule, $data, $field)
    {
        if (Helper::isValidEmail($value)) {
            return true;
        }

        return '邮箱格式不正确';
    }

    // 邮箱验证码验证
    protected function checkEmailCode($value, $rule, $data)
    {
        $verifyCode = model('EmailVerifyCode')->isVerify(['email' => $data['email'], 'type' => $data['smstype'], 'code' => $value]);
        if (!$verifyCode) {
            return '邮箱验证码不正确或已过期';
        }

        return true;
    }

    // 短信验证码验证
    protected function checkSmsCode($value, $rule, $data)
    {
        $verifyCode = model('VerifyCode')->isVerify(['mobile' => $data['mobile'], 'type' => $data['smstype'], 'code' => $value]);
        if (!$verifyCode) {
            return '短信验证码不正确或已过期';
        }

        return true;
    }

    /**
     * 密码验证
     * 参数依次为验证数据，验证规则，全部数据(数组)，字段名
     */
    protected function checkPWD($value, $rule, $data, $field)
    {
        if (Validator::isPWD($value)) {
            return true;
        }

        return '密码格式不正确';
    }

}