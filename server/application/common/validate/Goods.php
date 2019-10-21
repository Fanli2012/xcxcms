<?php

namespace app\common\validate;

use think\Validate;

class Goods extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['type_id', 'require|number|max:11', '栏目ID必填|栏目ID必须是数字|栏目ID格式不正确'],
        ['tuijian', 'number|max:11', '推荐等级必须是数字|推荐等级格式不正确'],
        ['click', 'number|max:11', '点击量必须是数字|点击量格式不正确'],
        ['title', 'require|max:150', '标题必填|标题不能超过150个字符'],
        ['seotitle', 'max:150', 'seo标题不能超过150个字符'],
        ['keywords', 'max:60', '关键词不能超过60个字符'],
        ['description', 'max:250', '描述不能超过250个字符'],
        ['sell_point', 'max:150', '卖点描述不能超过150个字符'],
        ['litpic', 'require|max:150', '请上传缩略图|缩略图不能超过150个字符'],
        ['goods_img', 'require|max:150', '请上传商品图片|商品的实际大小图片不能超过150个字符'],
        ['sn', 'require|max:60', '货号不能为空|货号不能超过60个字符'],
        ['price', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/|<=:market_price', '产品价格必填|产品价格只能带2位小数的数字|原价必须大于产品价格'],
        ['market_price', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/|>=:price', '原价必填|原价格式不正确，原价只能带2位小数的数字|原价必须大于产品价格'],
        ['cost_price', 'regex:/^\d{0,10}(\.\d{0,2})?$/', '成本价格格式不正确，成本价格只能带2位小数的数字'],
        ['shipping_fee', 'regex:/^\d{0,10}(\.\d{0,2})?$/', '运费格式不正确，运费只能带2位小数的数字'],
        ['goods_number', 'number|between:1,99999', '库存必须是数字|库存只能1-99999'],
        ['sale', 'number|max:11', '销量必须是数字|销量格式不正确'],
        ['warn_number', 'number|between:1,99', '商品报警数量必须是数字|商品报警数量只能1-99'],
        ['goods_weight', 'regex:/^\d{0,10}(\.\d{0,2})?$/', '重量格式不正确，重量只能带2位小数的数字'],
        ['point', 'number|between:1,99999', '购买该商品时每笔成功交易赠送的积分数量必须是数字|购买该商品时每笔成功交易赠送的积分数量只能1-99999'],
        ['comment_number', 'number|max:11', '评论次数必须是数字|评论次数格式不正确'],
        ['promote_price', 'regex:/^\d{0,10}(\.\d{0,2})?$/|<=:price', '促销价格格式不正确，促销价格只能带2位小数的数字|促销价格必须小于产品价格'],
        ['promote_start_date', 'number|egt:0|<:promote_end_date', '促销价格开始日期必须是数字|促销价格开始日期格式不正确|促销价格开始日期必须小于结束时间'],
        ['promote_end_date', 'number|egt:0|>:promote_start_date', '促销价格结束日期必须是数字|促销价格结束日期格式不正确|促销价格开始日期必须小于结束时间'],
        ['brand_id', 'number|max:11', '商品品牌ID必须是数字|商品品牌ID格式不正确'],
        ['user_id', 'number|max:11', '发布者ID必须是数字|发布者ID格式不正确'],
        ['listorder', 'number|max:11', '排序必须是数字|排序格式不正确'],
        ['status', 'in:0,1,2,3', '商品状态 0正常 1已删除 2下架 3申请上架'],
        ['shop_id', 'number|max:11', '店铺ID必须是数字|店铺ID格式不正确'],
        ['update_time', 'require|number|max:11', '更新时间必填|更新时间格式不正确|更新时间格式不正确'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['type_id', 'tuijian', 'click', 'title', 'seotitle', 'keywords', 'description', 'sell_point', 'litpic', 'goods_img', 'sn', 'price', 'market_price', 'cost_price', 'shipping_fee', 'goods_number', 'sale', 'warn_number', 'goods_weight', 'point', 'comment_number', 'promote_price', 'promote_start_date', 'promote_end_date', 'brand_id', 'user_id', 'listorder', 'status', 'update_time', 'add_time'],
        'edit' => ['type_id', 'tuijian', 'click', 'title', 'seotitle', 'keywords', 'description', 'sell_point', 'litpic', 'goods_img', 'sn', 'price', 'market_price', 'cost_price', 'shipping_fee', 'goods_number', 'sale', 'warn_number', 'goods_weight', 'point', 'comment_number', 'promote_price', 'promote_start_date', 'promote_end_date', 'brand_id', 'user_id', 'listorder', 'status', 'update_time'],
        'del' => ['id'],
    ];
}