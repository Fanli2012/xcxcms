<?php
namespace app\common\validate;

use think\Validate;

class Goods extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number','ID必填|ID必须是数字'],
        ['typeid', 'require|number','分类ID必填|分类ID必须是数字'],
        ['tuijian', 'number', '推荐等级必须是数字'],
        ['click', 'number', '点击量必须是数字'],
        ['title', 'require|max:150','标题必填|标题不能超过150个字符'],
        ['sn', 'max:60','货号不能超过60个字符'],
        ['price',  'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '产品价格必填|产品价格只能带2位小数的数字'],
        ['litpic', 'require|max:100','缩略图必须上传|缩略图不能超过100个字符'],
        ['pubdate', 'number', '更新时间格式不正确'],
        ['add_time', 'require|number', '添加时间必填|添加时间必须是数字'],
        ['keywords', 'max:60','关键词不能超过60个字符'],
        ['seotitle', 'max:150','seo标题不能超过150个字符'],
        ['description', 'max:250','描述不能超过250个字符'],
        ['status', 'in:0,1,2,3','商品状态 0正常 1已删除 2下架 3申请上架'],
        ['shipping_fee',  'regex:/^\d{0,10}(\.\d{0,2})?$/', '运费格式不正确，运费只能带2位小数的数字'],
        ['market_price',  'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '原价必填|原价格式不正确，原价只能带2位小数的数字'],
        ['goods_number', 'require|number|between:1,99999','库存必填|库存必须是数字|库存只能1-99999'],
        ['user_id', 'number', '发布者ID必须是数字'],
        ['sale', 'number|between:1,99999','销量必须是数字|销量只能1-99999'],
        ['cost_price',  'regex:/^\d{0,10}(\.\d{0,2})?$/', '成本价格格式不正确，成本价格只能带2位小数的数字'],
        ['goods_weight',  'regex:/^\d{0,10}(\.\d{0,2})?$/', '重量格式不正确，重量只能带2位小数的数字'],
        ['point', 'number|between:1,99999','购买该商品时每笔成功交易赠送的积分数量必须是数字|购买该商品时每笔成功交易赠送的积分数量只能1-99999'],
        ['comments', 'number|between:1,9999999','评论次数必须是数字|评论次数只能1-9999999'],
        ['promote_start_date', 'number','促销价格开始日期必须是数字'],
        ['promote_price',  'regex:/^\d{0,10}(\.\d{0,2})?$/', '促销价格格式不正确，促销价格只能带2位小数的数字'],
        ['promote_end_date', 'number','促销价格结束日期必须是数字'],
        ['goods_img', 'max:250','商品的实际大小图片不能超过250个字符'],
        ['warn_number', 'number|between:1,99','商品报警数量必须是数字|商品报警数量只能1-99'],
        ['listorder', 'number|between:1,9999','排序必须是数字|排序只能1-9999'],
        ['brand_id', 'number', '商品品牌ID必须是数字'],
    ];
    
    protected $scene = [
        'add' => ['typeid', 'title', 'tuijian', 'click', 'sn', 'price', 'litpic', 'pubdate', 'add_time', 'keywords', 'seotitle', 'description', 'status', 'shipping_fee', 'market_price', 'goods_number', 'user_id', 'sale', 'cost_price', 'goods_weight', 'point', 'comments', 'promote_start_date', 'promote_price', 'promote_end_date', 'goods_img', 'warn_number', 'listorder', 'brand_id'],
        'del' => ['id'],
    ];
}