const util = require('../../../../utils/util.js');

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
        util.AJAX("/product/productdetail", function (res) {
            // 重新写入数据
            that.setData({
                post: res.data.data
            });
        },{"id": id});
    }
})