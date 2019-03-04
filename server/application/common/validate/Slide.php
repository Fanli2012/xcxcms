<?php
namespace app\common\validate;

use think\Validate;

class Slide extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0','ID必填|ID必须是数字|ID格式不正确'],
        ['title', 'require|max:150','标题必填|标题不能超过150个字符'],
        ['url', 'max:150','跳转链接不能超过150个字符'],
        ['target', 'number|egt:0', '跳转方式必须是数字|跳转方式格式不正确'],
        ['group_id', 'number|egt:0', '分组ID必须是数字|分组ID格式不正确'],
        ['pic', 'require|max:100','图片地址必填|图片地址不能超过100个字符'],
        ['listorder', 'number|egt:0','排序必须是数字|排序格式不正确'],
        ['status', 'in:0,1,2','状态 0正常，1禁用'],
    ];
    
    protected $scene = [
        'add'  => ['title', 'url', 'target', 'group_id', 'pic', 'listorder', 'status'],
        'edit' => ['title', 'url', 'target', 'group_id', 'pic', 'listorder', 'status'],
        'del'  => ['id'],
    ];
}