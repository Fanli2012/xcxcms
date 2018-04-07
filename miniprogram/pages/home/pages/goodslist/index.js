const util = require('../../../../utils/util.js');

//获取应用实例
var app = getApp()

Page({
    data: {
        windowHeight: 0,
        dataList: [],
        loading: true,
        hasMore: false,
        limit: 8,
        offset: 0
    },
    onLoad: function () {
        var that = this;
        wx.getSystemInfo({
            success: function (res) {
                that.setData({
                    windowHeight: res.windowHeight + "px",
                });
            }
        });

        // 产品列表
        util.AJAX("/goods/index", function (res) {
            // 重新写入数据
            that.setData({
                dataList: res.data.data.list
            });
        }, { "limit": this.data.limit, "offset": this.data.offset });
    },
    // 上拉加载更多
    loadMore: function (e) {
        var that = this;
        this.setData({ offset: this.data.offset + this.data.limit, loading: false, hasMore: true });

        util.AJAX("/goods/index", function (res) {

            if (res.data.data.count > 0) {
                // 重新写入数据
                that.setData({
                    dataList: that.data.dataList.concat(res.data.data.list),
                    loading: true,
                    hasMore: false
                });
            }
        }, { "limit": this.data.limit, "offset": this.data.offset });
    },
    // 下拉刷新
    refresh: function (e) {
        var that = this;
        this.setData({ offset: 0, limit: 8, loading: true, hasMore: false });

        // 新闻列表
        util.AJAX("/goods/index", function (res) {
            // 重新写入数据
            that.setData({
                dataList: res.data.data.list
            });
        }, { "limit": this.data.limit, "offset": this.data.offset });
    }
})