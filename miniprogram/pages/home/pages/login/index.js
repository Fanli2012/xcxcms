const util = require('../../../../utils/util.js');
const config = require('../../../../config.js');

//获取应用实例
var app = getApp()

Page({
    data: {
        returnUrl: null
    },
    onLoad: function (options) {
        var that = this;
        var returnUrl = options.return_url;
        // 重新写入数据
        that.setData({
            returnUrl: returnUrl
        });
    },
    goTop: function () {
        wx.pageScrollTo({
            scrollTop: 0,
            duration: 300
        });
    },
    onGotUserInfo: function (e) {
        //console.log(e.detail.errMsg)
        //console.log(e.detail.userInfo)
        //console.log(e.detail.rawData)
        
        //var userInfo = wx.getStorageSync('userInfo');
        //console.log(userInfo);
        /* if (userInfo) {
            console.log(123);
            app.globalData.userInfo = e.detail.userInfo;
        } else {
            
        } */

        wx.login({
            success: function (res) {
                //console.log(res.code)
                if (res.code) {
                    var rawData = e.detail.rawData;
                    var signature = e.detail.signature;
                    var encryptedData = e.detail.encryptedData;
                    var iv = e.detail.iv;

                    wx.request({
                        //后台接口地址
                        url: config.appApiUrl + '/wechat/miniprogramWxlogin',
                        data: {
                            code: res.code,
                            rawData: rawData,
                            signature: signature,
                            iv: iv,
                            encryptedData: encryptedData
                        },
                        method: 'GET',
                        header: {
                            'content-type': 'application/json'
                        },
                        success: function (res2) {
                            //console.log(res2.data);
                            if (res2.data.code != 0) {
                                wx.showToast({
                                    title: '登录失败，请重新登录',
                                    image: '../../../../images/error1.png',
                                    duration: 2000
                                });

                                return false;
                            }

                            app.globalData.openId = res2.data.data.openId;
                            app.globalData.userInfo = res2.data.data;
                            wx.setStorageSync('openId', res2.data.data.openId);
                            wx.setStorageSync('userInfo', res2.data.data);
                        }
                    });
                }
            }
        });

        wx.showToast({
            title: '登录成功',
            icon: 'success',
            duration: 2000
        });

        if (this.data.returnUrl == null || this.data.returnUrl == '')
        {
            wx.navigateTo({
                url: '../index/index'
            });
        }
        else
        {
            wx.navigateTo({
                url: this.data.returnUrl
            });
        }
    }
})