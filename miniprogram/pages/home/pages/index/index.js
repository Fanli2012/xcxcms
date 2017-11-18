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
        title: "深圳市圆梦云科技有限公司",
        intro: "深圳市圆梦云科技有限公司是一家具有创新思维的互联网公司，主要提供的服务有互联网软件开发，包括微信公众平台服务，企业社区，商城产品，教育培训等。公司由有多年互联网经验的人员组成，致力于提供优质的互联网产品和服务，是国内最具实力的微信开发商、社区开发商。旗下主要开源产品WeiPHP下载量达百万级别，被众多开发者安装使用。",
        contab: "联系方式",
        address: "深圳市龙岗区坂田街道中兴路10号",
        mobile: "0755-23729769",
        email: "weiphp@weiphp.cn"
    },
    onLoad: function () {
        var that = this;

        // 幻灯片列表
        util.AJAX("/slide/slidelist", function (res) {
            // 重新写入数据
            that.setData({
                slideList: res.data.data
            });
        });

        // 新闻列表
        util.AJAX("/article/articlelist", function (res) {
            // 重新写入数据
            that.setData({
                newsList: res.data.data
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