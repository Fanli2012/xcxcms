const util = require('../../../../utils/util.js');

//获取应用实例
var app = getApp()

Page({
    data: {
        shop_id: 0,
    },
    onLoad: function (options) {
        var that = this;
        var shop_id = options.shop_id;

        // 重新写入数据
        that.setData({
            shop_id: shop_id
        });
    },
    formSubmit: function (e) {
        //console.log('form发生了submit事件，携带数据为：', e.detail.value);
        var data = e.detail.value;
        if (data.name == '') {
            wx.showToast({
                title: '请输入姓名',
                image: '../../../../images/error1.png',
                duration: 2000
            });

            return false;
        }

        if (data.phone == '') {
            wx.showToast({
                title: '请输入手机号码',
                image: '../../../../images/error1.png',
                duration: 2000
            });

            return false;
        }

        if (data.msg == '') {
            wx.showToast({
                title: '请输入内容',
                image: '../../../../images/error1.png',
                duration: 2000
            });

            return false;
        }

        var jump_url = '../shop/index?id=' + this.data.shop_id;
        var post_data = { "msg": data.msg, "name": data.name, "phone": data.phone, "shop_id": this.data.shop_id };
        util.AJAX("/guestbook/add", function (res) {
            //console.log(res);
            if (res.data.code == 0) {
                wx.showToast({
                    title: res.data.msg,
                    duration: 2000
                });

                setTimeout(function () {
                    wx.redirectTo({
                        url: jump_url
                    });
                }, 1000);

                return true;
            }

            wx.showToast({
                title: res.data.msg,
                image: '../../../../images/error1.png',
                duration: 2000
            });
        }, post_data, 'POST', { 'content-type': 'application/x-www-form-urlencoded' });
    },
    formReset: function () {
        console.log('form发生了reset事件')
    }
})