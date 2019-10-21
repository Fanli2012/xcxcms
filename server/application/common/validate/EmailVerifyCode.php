<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;

class EmailVerifyCode extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID必填|ID必须是数字|ID格式不正确'],
        ['code', 'require|max:10|number', '验证码必填|验证码不能超过10个字符|验证码格式不正确'],
        ['type', 'require|number|in:0,1,2,3,4,5,6,7,8,9', '验证码类型必填|验证码类型格式不正确|0通用，注册，1:手机绑定业务验证码，2:密码修改业务验证码'],
        ['email', 'require|email', '邮箱必填|邮箱格式不正确'],
        ['status', 'number|in:0,1', '验证码状态格式不正确|0:未使用 1:已使用'],
        ['add_time', 'require|number|egt:0', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
        ['expire_time', 'require|number|egt:0', '过期时间必填|过期时间格式不正确|过期时间格式不正确'],
        ['captcha', 'require|checkCaptcha', '验证码必填'],
    ];

    protected $scene = [
        'add' => ['code', 'type', 'email', 'add_time', 'expire_time'],
        'del' => ['id'],
        'get_verifycode_by_smtp' => ['email', 'type'],
        'check' => ['code', 'email', 'type'],
    ];

    /**
     * 邮箱验证
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
     * 手机号码验证
     * 参数依次为验证数据，验证规则，全部数据(数组)，字段名
     */
    protected function checkEmail($value, $rule, $data, $field)
    {
        if (Helper::isValidEmail($value)) {
            return true;
        }

        return '邮箱格式不正确';
    }

    // 图形验证码验证
    protected function checkCaptcha($value)
    {
        if (!captcha_check($value)) {
            return '图形验证码错误';
        }

        return true;
    }
}