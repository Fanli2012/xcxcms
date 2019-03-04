// pages/home/pages/user/index.js
const util = require('../../../../utils/util.js');
const config = require('../../../../config.js');

//获取应用实例
var app = getApp()

Page({
    /**
     * 页面的初始数据
     */
    data: {
        userInfo: null
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        var that = this;

        that.setData({
            sysconfigInfo: wx.getStorageSync('sysconfigInfo')
        });

        var userInfo = wx.getStorageSync('userInfo') || null;
        //console.log(userInfo);
        if (userInfo == "" || userInfo == null || userInfo == undefined || userInfo.length == 0) {
            wx.navigateTo({
                url: '../login/index'
            });

            return false;
        }

        var timestamp = Date.parse(new Date()).toString().substr(0, 10);
        //console.log(timestamp);
        if (userInfo.token.expired_time < timestamp) {
            wx.navigateTo({
                url: '../login/index'
            });
        }

        // 重新写入数据
        that.setData({
            userInfo: userInfo
        });
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function() {

    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function() {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function() {

    },
    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function() {
        wx.showToast({
            title: '玩命加载中...',
            icon: 'loading'
        });

        var timer = setTimeout(function() {
            wx.hideToast();
        }, 2000);

        clearTimeout(timer);

        wx.stopPullDownRefresh();
    },
    stopPullDownRefresh: function() {
        wx.stopPullDownRefresh({
            complete: function(res) {
                wx.hideToast();
                //console.log(res, new Date());
            }
        });
    },
    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function() {

    },
    openAlert: function() {
        var sysconfigInfo = wx.getStorageSync('sysconfigInfo');
        wx.showModal({
            content: sysconfigInfo.miniprogram_des,
            showCancel: false,
            success: function(res) {
                if (res.confirm) {
                    console.log('用户点击确定')
                }
            }
        });
    },
    //打电话
    makePhoneCall: function() {
        wx.makePhoneCall({
            phoneNumber: this.data.sysconfigInfo.cms_manage_phone,
            success: function() {
                console.log("成功拨打电话")
            }
        })
    },
    logout: function() {
        wx.removeStorageSync('userInfo');

        wx.showToast({
            title: '退出成功',
            icon: 'success',
            duration: 2000
        });

        wx.navigateTo({
            url: '../index/index'
        });
    }
})