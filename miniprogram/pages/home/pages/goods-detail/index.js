const util = require('../../../../utils/util.js');
var WxParse = require('../../../../vendor/wxParse/wxParse.js');

//获取应用实例
var app = getApp()

Page({
  data: {
    post: {},
    windowWidth: wx.getSystemInfoSync().windowWidth, // 宽度,
    windowHeight: wx.getSystemInfoSync().windowHeight, // 高度,
  },
  onLoad: function(options) {
    var that = this;
    var id = options.id;

    // 详情
    util.AJAX("/goods/detail", function(res) {
      WxParse.wxParse('goods', 'html', res.data.data.content, that, 5);
      console.log(res.data.data);
      // 重新写入数据
      that.setData({
        post: res.data.data
      });
    }, {
      "id": id
    });
  },
  //分享
  onShareAppMessage: function(res) {
    if (res.from === 'button') {
      // 来自页面内转发按钮
      console.log(res.target)
    }
    return {
      title: this.data.post.title,
      path: '/pages/home/pages/goods-detail/index?id=' + this.data.post.id
    }
  },
  open: function() {
    wx.showActionSheet({
      itemList: ['电话：' + this.data.post.shop.contact_information, 'B', 'C'],
      success: function(res) {
        if (!res.cancel) {
          console.log(res.tapIndex)
        }
      }
    });
  },
  goback_home: function() {
    wx.switchTab({
      url: '../index/index'
    });
  },
  //点击图片预览
  clickImagePreview: function(event) {
    var that = this;
    var imgList = [];

    var src = event.currentTarget.dataset.src; //获取data-src
    var list = that.data.post.goods_img_list;
    if (list.length > 0) {
      for (var i = 0; i < list.length; i++) {
        imgList.push(list[i]['url']);
      }
    } else {
      imgList.push(src);
    }

    //注意：wx.previewimage预览返回会触发onShow方法，所以初始化尽量放在onLoad方法
    //图片预览
    wx.previewImage({
      current: src, // 当前显示图片的http链接
      urls: imgList // 需要预览的图片http链接列表
    });
  }
})