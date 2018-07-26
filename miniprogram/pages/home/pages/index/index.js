const util = require('../../../../utils/util.js');

//获取应用实例
var app = getApp()

Page({
    data: {
        // 幻灯片数据
        slideList: [],
        // 最新动态列表
        newsList: [],
        // 产品列表
        goodsList: [],

        img: '../../images/logo.png',
        title: '繁橙工作室',
        intro: '中小企业解决方案：PC+手机+微信网站+小程序+APP，专注于B2C商城网站建设、微信开发、网站设计开发服务为一体的互联网技术服务定制。',
        contact: '范例',
        mobile: "15280719357",
        email: "374861669@qq.com",
        address: "厦门软件园二期48号",
        windowWidth: wx.getSystemInfoSync().windowWidth, // 宽度,
        windowHeight: wx.getSystemInfoSync().windowHeight, // 高度,
    },
    onLoad: function () {
        var that = this;
        /* wx.getSystemInfo({
            success: function (res) {
                that.setData({
                    windowHeight: res.windowHeight + "px",
                    windowWidth: res.windowWidth + "px",
                });
            }
        }); */

        // 幻灯片列表
        util.AJAX("/slide/index", function (res) {
            //console.log(JSON.stringify(res));
            // 重新写入数据
            that.setData({
                slideList: res.data.data.list
            });
        }, { "limit": 5 });

        // 新闻列表
        util.AJAX("/article/index", function (res) {
            var newsList = res.data.data.list;
            if (newsList) {
                for (var i = 0; i < newsList.length; i++) {
                    var time = util.getTime(newsList[i]['pubdate']);
                    newsList[i]['pubdate'] = time['Y'] + '-' + time['m'] + '-' + time['d'];
                }
            }

            // 重新写入数据
            that.setData({
                newsList: newsList
            });
        });

        // 产品列表
        util.AJAX("/goods/index", function (res) {
            var goodsList = res.data.data.list;
            if (goodsList) {
                for (var i = 0; i < goodsList.length; i++) {
                    var time = util.getTime(goodsList[i]['pubdate']);
                    goodsList[i]['pubdate'] = time['Y'] + '-' + time['m'] + '-' + time['d'];
                }
            }

            // 重新写入数据
            that.setData({
                goodsList: goodsList
            });
        }, { "limit": 8 });
    },
    //打电话
    makePhoneCall: function () {
        wx.makePhoneCall({
            phoneNumber: this.data.mobile,
            success: function () {
                console.log("成功拨打电话")
            }
        })
    }
})