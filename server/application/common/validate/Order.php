<?php

namespace app\common\validate;

use think\Validate;

class Order extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID必填|ID必须是数字|ID格式不正确'],
        ['order_sn', 'require|max:30', '订单号必填|订单号不能超过30个字符'],
        ['user_id', 'require|number|max:11', '用户ID必填|用户ID必须是数字|用户ID格式不正确'],
        ['shop_id', 'number|max:11', '商家ID必须是数字|商家ID格式不正确'],
        ['order_status', 'in:0,1,2,3', '订单状态:0生成订单,1已取消(客户触发),2无效(管理员触发),3完成订单'],
        ['refund_status', 'in:0,1,2,3', '退货退款(订单完成后)，0无退货，1有退货，2退货成功，3拒绝'],
        ['shipping_status', 'in:0,1,2', '配送情况:0未发货,1已发货,2已收货'],
        ['pay_status', 'in:0,1', '支付状态:0未付款,1已付款'],
        ['goods_amount', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '商品的总金额必填|商品的总金额格式不正确'],
        ['order_amount', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '应付金额必填|应付金额格式不正确'],
        ['discount', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '优惠金额必填|优惠金额格式不正确'],
        ['pay_money', 'regex:/^\d{0,10}(\.\d{0,2})?$/', '支付金额必填|支付金额格式不正确'],
        ['payment_id', 'number|max:11', '支付方式ID必须是数字|支付方式ID格式不正确'],
        ['pay_time', 'number|max:11', '订单支付时间格式不正确|订单支付时间格式不正确'],
        ['pay_name', 'max:30', '支付方式名称不能超过30个字符'],
        ['trade_no', 'max:60', '支付订单号不能超过60个字符'],
        ['shipping_name', 'max:20', '配送方式名称不能超过20个字符'],
        ['shipping_id', 'number|max:11', '配送方式ID必须是数字|配送方式ID格式不正确'],
        ['shipping_sn', 'max:30', '支付订单号不能超过30个字符'],
        ['shipping_fee', 'regex:/^\d{0,10}(\.\d{0,2})?$/', '配送费用格式不正确'],
        ['shipping_time', 'number|max:11', '发货时间格式不正确|发货时间格式不正确'],
        ['name', 'max:30', '收货人姓名不能超过30个字符'],
        ['country_id', 'require|number|max:11', '国家ID必填|国家ID格式不正确|国家ID格式不正确'],
        ['province_id', 'require|number|max:11', '省份ID必填|省份ID格式不正确|省份ID格式不正确'],
        ['city_id', 'require|number|max:11', '城市ID必填|城市ID格式不正确|城市ID格式不正确'],
        ['district_id', 'require|number|max:11', '区域ID必填|区域ID格式不正确|区域ID格式不正确'],
        ['address', 'require|max:240', '详细地址不能超过240个字符'],
        ['zipcode', 'max:10', '邮编不能超过10个字符'],
        ['mobile', 'require|max:20', '电话不能超过20个字符'],
        ['message', 'max:240', '买家留言不能超过240个字符'],
        ['is_comment', 'in:0,1', '是否评论，1已评价'],
        ['integral_money', 'regex:/^\d{0,10}(\.\d{0,2})?$/', '使用积分金额格式不正确'],
        ['integral', 'number|max:11', '使用的积分的数量格式不正确|使用的积分的数量格式不正确'],
        ['bonus_money', 'regex:/^\d{0,10}(\.\d{0,2})?$/', '使用优惠劵支付金额格式不正确'],
        ['bonus_id', 'number|max:11', '优惠券ID格式不正确|优惠券ID格式不正确'],
        ['note', 'max:100', '商家/后台操作备注不能超过100个字符'],
        ['invoice', 'in:0,1,2', '发票类型：0不索要，1个人，2企业'],
        ['invoice_title', 'max:100', '发票抬头不能超过100个字符'],
        ['invoice_taxpayer_number', 'max:100', '纳税人识别号不能超过100个字符'],
        ['place_type', 'in:0,1,2,3,4,5', '订单来源：1pc，2weixin，3app，4wap，5miniprogram'],
        ['update_time', 'require|number|max:11', '更新时间必填|更新时间格式不正确|更新时间格式不正确'],
        ['add_time', 'require|number|max:11', '添加时间必填|添加时间格式不正确|添加时间格式不正确'],
        ['delete_time', 'require|number|max:11', '删除时间必填|删除时间格式不正确|删除时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['order_sn', 'user_id', 'shop_id', 'goods_amount', 'order_amount', 'discount', 'name', 'country_id', 'province_id', 'city_id', 'district_id', 'address', 'zipcode', 'mobile', 'message', 'integral_money', 'integral', 'bonus_money', 'bonus_id', 'invoice', 'invoice_title', 'invoice_taxpayer_number', 'place_type'],
        'edit' => ['order_sn', 'user_id', 'shop_id', 'goods_amount', 'order_amount', 'discount', 'name', 'country_id', 'province_id', 'city_id', 'district_id', 'address', 'zipcode', 'mobile', 'message', 'integral_money', 'integral', 'bonus_money', 'bonus_id', 'invoice', 'invoice_title', 'invoice_taxpayer_number', 'place_type'],
        'del' => ['id'],
    ];
}