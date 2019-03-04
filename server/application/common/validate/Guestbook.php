<?php
namespace app\common\validate;
use think\Validate;
use app\common\lib\Helper;

class Guestbook extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0','ID必填|ID必须是数字|ID格式不正确'],
        ['title', 'max:150','标题必填|标题不能超过150个字符'],
        ['msg', 'require|max:250','内容必填|内容不能超过250个字符'],
        ['name', 'require|max:30','姓名必填|姓名不能超过30个字符'],
        ['mobile', 'require|max:20|checkMobile','手机号必填|手机号不能超过20个字符'],
        ['email', 'email','邮箱格式不正确'],
        ['status', 'number|in:0,1','是否阅读必须是数字|是否阅读，默认0未阅读'],
        ['shop_id', 'number|egt:0', '店铺ID必须是数字|店铺ID格式不正确'],
        ['add_time', 'require|number|egt:0', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];
    
    protected $scene = [
        'add'  => ['title', 'msg', 'name', 'mobile', 'email', 'status', 'shop_id', 'add_time'],
        'edit' => ['title', 'msg', 'name', 'mobile', 'email', 'status', 'shop_id', 'add_time'],
        'del'  => ['id'],
    ];
    
    /**
     * 手机号码验证
     * 参数依次为验证数据，验证规则，全部数据(数组)，字段名
     */
    protected function checkMobile($value,$rule,$data,$field)
    {
        if(Helper::isValidMobile($value))
        {
            return true;
        }
        
        return '手机号码格式不正确';
    }
}