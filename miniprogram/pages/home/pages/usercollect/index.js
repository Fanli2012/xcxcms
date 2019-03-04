// pages/home/pages/user/index.js
const util = require('../../../../utils/util.js');
const config = require('../../../../config.js');

//获取应用实例
var app = getApp();

var sliderWidth = 96; // 需要设置slider的宽度，用于计算中间位置

Page({
    data: {
        tabs: ["企业收藏", "用户收藏"],
        activeIndex: 1,
        sliderOffset: 0,
        sliderLeft: 0,
        limit: 8,
        offset: 0,
        userInfo: null,
        collectUserList: null
    },
    onLoad: function () {
        var that = this;
        wx.getSystemInfo({
            success: function (res) {
                that.setData({
                    sliderLeft: (res.windowWidth / that.data.tabs.length - sliderWidth) / 2,
                    sliderOffset: res.windowWidth / that.data.tabs.length * that.data.activeIndex
                });
            }
        });

        that.setData({
            userInfo: wx.getStorageSync('userInfo')
        });

        // 企业收藏列表
        util.AJAX("/user_collect/index", function (res) {
            console.log(res.data.data.list);
            // 重新写入数据
            that.setData({
                collectUserList: res.data.data.list
            });
        }, { "limit": this.data.limit, "offset": this.data.offset, "access_token": this.data.userInfo.token.access_token });
    },
    tabClick: function (e) {
        this.setData({
            sliderOffset: e.currentTarget.offsetLeft,
            activeIndex: e.currentTarget.id
        });
    }
});