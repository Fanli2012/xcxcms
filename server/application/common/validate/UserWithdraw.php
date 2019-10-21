<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class UserWithdraw extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['user_id', 'require|number|max:11', '用户ID必填|用户ID必须是数字|用户ID格式不正确'],
        ['money', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '提现金额必填|提现金额只能带2位小数的数字'],
        ['name', 'max:30', '姓名格式不正确'],
        ['status', 'in:0,1,2,3,4', '提现状态：0未处理,1处理中,2成功,3取消，4拒绝'],
        ['note', 'max:250', '用户备注格式不正确'],
        ['re_note', 'max:250', '回复备注信息格式不正确'],
        ['bank_name', 'max:60', '银行名称格式不正确'],
        ['bank_place', 'max:150', '开户行格式不正确'],
        ['account', 'max:30', '支付宝账号或者银行卡号格式不正确'],
        ['method', 'require|max:20', '提现方式必填|提现方式格式不正确'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
        ['update_time', 'require|number|max:11', '更新时间必填|更新时间格式不正确|更新时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['user_id', 'money', 'name', 'status', 'note', 'account', 'method'],
        'edit' => ['user_id', 'money', 'name', 'status', 'note', 'account', 'method'],
        'del' => ['user_id'],
    ];
}