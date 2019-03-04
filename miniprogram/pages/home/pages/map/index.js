Page({
    data: {
        latitude: 24.483350,
        longitude: 118.186900,
        markers: [{
            latitude: 24.483350,
            longitude: 118.186900,
            title: '厦门软件园二期48号'
        }],
        contact: '范例',
        mobile: "15280719357",
        email: "374861669@qq.com",
        address: "厦门软件园二期48号",
    },
    onLoad: function(options) {
        var that = this;
        var shopDetail = wx.getStorageSync('shopDetail');
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
    },
    //打电话
    makePhoneCall: function() {
        wx.makePhoneCall({
            phoneNumber: this.data.mobile,
            success: function() {
                console.log("成功拨打电话")
            }
        })
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