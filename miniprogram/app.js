const util = require('./utils/util.js');
const config = require('./config.js');

//app.js
App({
    /**
     * 页面的初始数据
     */
    data: {
        // 配置参数
        sysconfigInfo: null
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLaunch: function(options) {
        var that = this;
        
        //wx.clearStorageSync();
        //检测当前用户登录态是否有效
        /* wx.checkSession({
            success: function() {
                //session 未过期，并且在本生命周期一直有效
            },
            fail: function() {
                //登录态过期，重新登录
                wx.login({
                    success: function(res) {
                        if (res.code) {
                            //发起网络请求
                            wx.request({
                                url: config.appApiUrl + '/wechat/miniprogramWxlogin',
                                data: {
                                    code: res.code
                                },
                                success: function(res) {
                                    console.log(res.data)
                                }
                            });
                        } else {
                            console.log('登录失败！' + res.errMsg)
                        }
                    }
                });
            }
        }); */
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
    globalData: {
        userInfo: null,
        hasLogin: false,
        openId: null,
        shop_id: config.appShopId,
        shopDetail: {},
        //判断小程序的API，回调，参数，组件等是否在当前版本可用。
        canIUse: wx.canIUse('button.open-type.getUserInfo')
    }
})