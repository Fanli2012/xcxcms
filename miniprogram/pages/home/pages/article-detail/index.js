const util = require('../../../../utils/util.js');
var WxParse = require('../../../../vendor/wxParse/wxParse.js');

//获取应用实例
var app = getApp()

Page({
  data: {
    post: {}
  },
  onLoad: function(options) {
    var that = this;
    var id = options.id;

    var obj = wx.getLaunchOptionsSync();
    that.setData({
      scene: obj.scene
    });

    util.AJAX("/article/detail", function(res) {
      WxParse.wxParse('article', 'html', res.data.data.content, that, 5);

      //时间格式化
      var time = util.getTime(res.data.data.updated_at);
      res.data.data.updated_at = time['Y'] + '-' + time['m'] + '-' + time['d'];

      //console.log(res);
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
      path: '/pages/home/pages/article-detail/index?id=' + this.data.post.id
    }
  },
  goTop: function() {
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 300
    });
  },
  goback_home: function() {
    wx.switchTab({
      url: '../index/index'
    });
  }
})