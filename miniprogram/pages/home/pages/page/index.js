const util = require('../../../../utils/util.js');
var WxParse = require('../../../../vendor/wxParse/wxParse.js');

//获取应用实例
var app = getApp()

Page({
    data: {
        motto: 'Hello World',
        shopDetail: {}
    },
    onLoad: function () {
        var that = this;
        var shopDetail = wx.getStorageSync('shopDetail');
        // 店铺详情
        that.setData({
            shopDetail: shopDetail
        });
        
        WxParse.wxParse('article', 'html', shopDetail.content, that, 5);
    }
})