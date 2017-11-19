const util = require('../../../../utils/util.js');

//获取应用实例
var app = getApp()

Page({
    data: {
        dataList: []
    },
    onLoad: function () {
        var that = this;

        // 新闻列表
        util.AJAX("/article/articlelist", function (res) {
            // 重新写入数据
            that.setData({
                dataList: res.data.data
            });
        }, {"limit":20});
    }
})