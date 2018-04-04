<?php
namespace app\common\validate;

use think\Validate;

class Product extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number','ID必填|ID必须是数字'],
        ['typeid', 'require|number','分类ID必填|分类ID必须是数字'],
        ['tuijian', 'number', '推荐等级必须是数字'],
        ['click', 'number', '点击量必须是数字'],
        ['title', 'require|max:150','标题必填|标题不能超过150个字符'],
        ['serial_no', 'max:100','货号不能超过100个字符'],
        ['price',  'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '售价必填|售价只能带2位小数的数字'],
        ['litpic', 'max:100','缩略图不能超过100个字符'],
        ['pubdate', 'number', '更新时间格式不正确'],
        ['addtime', 'require|number', '添加时间必填|添加时间必须是数字'],
        ['keywords', 'max:60','关键词不能超过60个字符'],
        ['seotitle', 'max:150','seo标题不能超过150个字符'],
        ['description', 'max:250','描述不能超过250个字符'],
        ['status', 'in:0,1,2,3','商品状态 0正常 1已删除 2下架 3申请上架'],
        ['delivery_fee',  'regex:/^\d{0,10}(\.\d{0,2})?$/', '运费格式不正确，运费只能带2位小数的数字'],
        ['ismobile', 'in:0,1','手机专享，0否 1是'],
        ['origin_price',  'regex:/^\d{0,10}(\.\d{0,2})?$/', '原价格式不正确，原价只能带2位小数的数字'],
        ['user_id', 'number', '发布者ID必须是数字'],
        ['inventory', 'number|between:1,99999','库存必须是数字|库存只能1-99999'],
        ['sales', 'number|between:1,99999','销量必须是数字|销量只能1-99999'],
    ];
    
    protected $scene = [
        'add' => ['typeid', 'title', 'tuijian', 'click', 'serial_no', 'price', 'litpic', 'pubdate', 'addtime', 'keywords', 'seotitle', 'description', 'status', 'user_id', 'delivery_fee', 'ismobile', 'origin_price', 'inventory', 'sales'],
        'del' => ['id'],
    ];
}