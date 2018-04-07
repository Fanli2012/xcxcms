const util = require('../../../../utils/util.js');
var WxParse = require('../../../../vendor/wxParse/wxParse.js');

//获取应用实例
var app = getApp()

Page({
    data: {
        post: {}
    },
    onLoad: function (options) {
        var that = this;
        var id = options.id;

        // 详情
        util.AJAX("/goods/detail", function (res) {
            WxParse.wxParse('goods', 'html', res.data.data.body, that, 5);
            
            // 重新写入数据
            that.setData({
                post: res.data.data
            });
        },{"id": id});
    }
})