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
        productList: [],

        img: '../../images/logo.png',
        title: '繁橙工作室',
        intro: '中小企业解决方案：PC+手机+微信网站+小程序+APP，专注于B2C商城网站建设、微信开发、网站设计开发服务为一体的互联网技术服务定制。',
        contab: "联系方式",
        address: "厦门软件园二期48号",
        mobile: "15280719357",
        email: "374861669@qq.com"
    },
    onLoad: function () {
        var that = this;

        // 幻灯片列表
        util.AJAX("/slide/index", function (res) {
            //console.log(res);
            // 重新写入数据
            that.setData({
                slideList: res.data.data.list
            });
        });

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
        // util.AJAX("/slide/slidelist", function (res) {
        //     // 重新写入数据
        //     that.setData({
        //         slideList: res.data.data
        //     });
        // });
    }
})