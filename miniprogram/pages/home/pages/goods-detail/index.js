const util = require('../../../../utils/util.js');
var WxParse = require('../../../../vendor/wxParse/wxParse.js');

//获取应用实例
var app = getApp()

Page({
    data: {
        post: {},
        windowWidth: wx.getSystemInfoSync().windowWidth, // 宽度,
        windowHeight: wx.getSystemInfoSync().windowHeight, // 高度,
    },
    onLoad: function (options) {
        var that = this;
        var id = options.id;

        // 详情
        util.AJAX("/goods/detail", function (res) {
            WxParse.wxParse('goods', 'html', res.data.data.content, that, 5);
            console.log(res.data.data);
            // 重新写入数据
            that.setData({
                post: res.data.data
            });
        },{"id": id});
    },
    //分享
    onShareAppMessage: function (res) {
        return {
            title: this.data.post.title,
            path: '/pages/home/pages/goods-detail/index?id=' + this.data.post.id
        }
    },
    open: function () {
        wx.showActionSheet({
            itemList: ['电话：'+this.data.post.shop.contact_information, 'B', 'C'],
            success: function (res) {
                if (!res.cancel) {
                    console.log(res.tapIndex)
                }
            }
        });
    }
})