const util = require('../../../../utils/util.js');

//获取应用实例
var app = getApp()

Page({
    data: {
        // 幻灯片数据
        slideList: [],
        // 最新动态列表
        articleList: [],
        // 产品列表
        goodsList: [],
        // 店铺详情        
        shopDetail: {},
        windowWidth: wx.getSystemInfoSync().windowWidth, // 宽度,
        windowHeight: wx.getSystemInfoSync().windowHeight, // 高度
        canIUse: app.globalData.canIUse
    },
    onLoad: function(options) {
        var that = this;
        var id = app.globalData.shop_id;

        // 店铺详情
        util.AJAX("/index/config", function(res) {
            var shopDetail = res.data.data;
            // 重新写入数据
            wx.setStorageSync('shopDetail', shopDetail);
            app.globalData.shopDetail = shopDetail;
            that.setData({
                shopDetail: shopDetail,
            });

            //位置坐标
            that.setData({
                latitude: shopDetail.point_lat,
                longitude: shopDetail.point_lng,
                markers: [{
                    latitude: shopDetail.point_lat,
                    longitude: shopDetail.point_lng,
                    title: shopDetail.company_name
                }],
            });
            // 动态修改页面标题
            wx.setNavigationBarTitle({
                title: shopDetail.company_name
            })
        });

        // 查看是否授权
        /* wx.getSetting({
            success: function (res) {
                if (res.authSetting['scope.userInfo']) {
                    // 已经授权，可以直接调用 getUserInfo 获取头像昵称
                    wx.getUserInfo({
                        success: function (res) {
                            console.log(res.userInfo)
                        }
                    })
                }
            }
        }); */

        /* wx.getSystemInfo({
            success: function (res) {
                that.setData({
                    windowHeight: res.windowHeight + "px",
                    windowWidth: res.windowWidth + "px",
                });
            }
        }); */

        // 新闻列表
        util.AJAX("/article/index", function(res) {
            if (res.data.code == 0 && res.data.data.count > 0) {
                var articleList = res.data.data.list;
                for (var i = 0; i < articleList.length; i++) {
                    var time = util.getTime(articleList[i]['updated_at']);
                    articleList[i]['updated_at'] = time['Y'] + '-' + time['m'] + '-' + time['d'];
                }

                // 重新写入数据
                that.setData({
                    articleList: articleList
                });
            }
        }, {
            "limit": 8
        });

        // 产品列表
        util.AJAX("/goods/index", function(res) {
            if (res.data.code == 0 && res.data.data.count > 0) {
                var goodsList = res.data.data.list;
                for (var i = 0; i < goodsList.length; i++) {
                    var time = util.getTime(goodsList[i]['pubdate']);
                    goodsList[i]['pubdate'] = time['Y'] + '-' + time['m'] + '-' + time['d'];
                }

                // 重新写入数据
                that.setData({
                    goodsList: goodsList
                });
            }
        }, {
            "limit": 4
        });
    },
    onShow: function() {

    },
    onReady: function() {

    },
    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },
    //打电话
    makePhoneCall: function() {
        wx.makePhoneCall({
            phoneNumber: this.data.shopDetail.contact_information,
            success: function() {
                console.log("成功拨打电话")
            }
        })
    },
    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function(res) {
        if (res.from === 'button') {
            // 来自页面内转发按钮
            console.log(res.target);
        }

        return {
            title: this.data.shopDetail.company_name,
            desc: this.data.shopDetail.introduction,
            path: '/pages/home/pages/index/index'
        }
    },
    //点击图片预览
    clickImagePreview: function(event) {
        var src = event.currentTarget.dataset.src; //获取data-src
        var imgList = [];
        imgList.push(src);
        //var imgList = event.currentTarget.dataset.list; //获取data-list
        //图片预览
        wx.previewImage({
            //current: src, // 当前显示图片的http链接
            urls: imgList // 需要预览的图片http链接列表
        });
    },
    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    /* onPullDownRefresh: function () {
        wx.showToast({
            title: '玩命加载中...',
            icon: 'loading'
        });

        var timer = setTimeout(function () {
            wx.hideToast();
        }, 2000);

        clearTimeout(timer);

        wx.stopPullDownRefresh();
    },
    stopPullDownRefresh: function () {
        wx.stopPullDownRefresh({
            complete: function (res) {
                wx.hideToast();
                //console.log(res, new Date());
            }
        });
    }, */
    //页面跳转
    jumpTabBarPage: function (event) {
        var url = event.currentTarget.dataset.url; //获取data-url
        wx.switchTab({
            url: url
        });
    },
    //复制到剪切板
    copyTBL: function(e) {
        wx.setClipboardData({
            data: e.currentTarget.dataset.clipboarddata,
            success: function(res) {
                wx.getClipboardData({
                    success: function(res) {
                        wx.showToast({
                            title: '已复制',
                            icon: 'success',
                            duration: 2000
                        })
                    }
                })
            }
        })
    }
})