<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class SmsLog extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID必填|ID必须是数字|ID格式不正确'],
        ['mobile', 'require|max:20|checkMobile', '手机号必填|手机号不能超过20个字符'],
        ['content', 'require|max:200', '发送的内容必填|发送的内容不能超过200个字符'],
        ['status', 'number|in:1,2', '状态格式不正确|状态：1成功，2失败'],
        ['result', 'max:500', '返回结果不能超过500个字符'],
        ['add_time', 'require|number|egt:0', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['mobile', 'content', 'status', 'result', 'add_time'],
        'edit' => ['mobile', 'content', 'status', 'result', 'add_time'],
        'del' => ['id'],
    ];

    /**
     * 手机号码验证
     * 参数依次为验证数据，验证规则，全部数据(数组)，字段名
     */
    protected function checkMobile($value, $rule, $data, $field)
    {
        if (Validator::isMobile($value)) {
            return true;
        }

        return '手机号码格式不正确';
    }
}