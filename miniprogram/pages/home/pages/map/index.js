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
