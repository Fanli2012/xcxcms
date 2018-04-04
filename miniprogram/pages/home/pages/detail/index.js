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
        
        util.AJAX("/article/detail", function (res) {
            WxParse.wxParse('article', 'html', res.data.data.body, that, 5);
            
            //console.log(res);
            // 重新写入数据
            that.setData({
                post: res.data.data
            });
        },{"id": id});
    }
})