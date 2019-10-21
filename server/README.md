# nbnbk
基于thinkphp5的cms


## 效果截图

PC端

![alt text](public/images/screenshots.jpg "网站截图")

WAP端

![alt text](public/images/screenshots-wap.gif "WAP首页")

微商城

![alt text](public/images/screenshots-wsc.jpg "商品列表")
![alt text](public/images/screenshots-wsc.png "个人中心")

后台管理

![alt text](public/images/screenshots-admin.jpg "后台管理")


## 说明

1、基于ThinkPHP5.0.24

2、PHP+Mysql

3、后台登录：http://www.nbnbk3.com/fladmin/login，账号：admin888，密码：123456

4、恢复后台默认账号密码：http://www.nbnbk3.com/fladmin/login/recoverpwd

5、tp5开源cms，适合博客、中小企业建站二次开发。

6、http://www.nbnbk3.com只是示例域名，需换成自己绑定的域名

注意：WAP端的域名通常是PC端的子域名，这里PC端的域名是www.nbnbk3.com，WAP端的域名是m.nbnbk3.com，子域名不是m就要修改application/route.php下的m

<strong>PC入口</strong>：http(s)://+PC域名+/

<strong>WAP入口</strong>：http(s)://+WAP域名+/，WAP域名解析与PC域名一致，都是指向同一目录下

<strong>微商城入口</strong>：http(s)://+PC域名+/weixin，支付仅支持微信支付。

7、后台功能
1) 文章管理：增删改查，栏目管理
2) 单页管理
3) RBAC权限管理，管理员/角色管理，权限授权
4) 商品管理：商品品牌，商品分类
5) 订单管理：订单列表，订单详情，订单导出EXCEL
6) 优惠券/红包管理：列表，添加，修改，删除
7) 会员管理：会员等级，余额记录，提现，充值
8) 轮播图
9) 友情链接
10) 系统参数配置

8、前台功能
1) 公司介绍
2) 产品中心
3) 新闻动态
4) 联系我们
5) 友情链接


## 安装

跟thinkphp5安装一样，只是多了一步数据库导入

1、 导入数据库

1) 打开根目录下的nbnbk.sql文件，替换文件里面的 http://www.nbnbk3.com 为自己的站点根网址，格式：http(s)://+域名

2) 导入数据库，导入完成之后，因为数据表有索引，需要修复表不然很容易报错

2、 修改数据库连接参数

打开/application/database.php文件,修改相关配置

3、 根目录执行 Composer install 或 Composer update 示例：php composer.phar install 或 php composer.phar update

4、 登录后台->系统设置->系统配置参数，更新配置：http://www.nbnbk3.com/fladmin/index/upcache

Linux系统文件/目录权限

 + extend/wxJsSdk目录设置成可读写777
 + public/index.php文件设置成只读444


## 注意

站点只能放在根目录

public目录做为网站根目录，入口文件在 public/index.php


## 环境要求

* php5.4.0+
* mysql5.6+
* PDO PHP Extension
* MBstring PHP Extension
* CURL PHP Extension
* 打开rewrite

> ThinkPHP5的运行环境要求PHP5.4以上。


## 目录结构

初始的目录结构如下：

~~~
www  WEB部署目录（或者子目录）
├─application           应用目录
│  ├─api                API目录主要提供接口
│  ├─common             公共模块目录（可以更改）
│  ├─extra              扩展配置文件
│  ├─fladmin            后台管理目录
│  ├─index              PC端目录
│  ├─wap                移动端目录
│  ├─weixin             微信商城目录
│  ├─module_name        模块目录
│  │  ├─config.php      模块配置文件
│  │  ├─common.php      模块函数文件
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  ├─view            视图目录
│  │  └─ ...            更多类库目录
│  │
│  ├─command.php        命令行工具配置文件
│  ├─common.php         公共函数文件
│  ├─config.php         公共配置文件
│  ├─route.php          路由配置文件
│  ├─tags.php           应用行为扩展定义文件
│  └─database.php       数据库配置文件
│
├─tests                 单元测试目录
├─thinkphp              ThinkPHP5.0.24框架系统目录
│  ├─lang               语言文件目录
│  ├─library            框架类库目录
│  │  ├─think           Think类库包目录
│  │  └─traits          系统Trait目录
│  │
│  ├─tpl                系统模板目录
│  ├─base.php           基础定义文件
│  ├─console.php        控制台入口文件
│  ├─convention.php     框架惯例配置文件
│  ├─helper.php         助手函数文件
│  ├─phpunit.xml        phpunit配置文件
│  └─start.php          框架入口文件
│
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
│
├─extend                扩展类库目录
├─runtime               应用的运行时目录（可写，可定制）
├─vendor                第三方类库目录（Composer依赖库）
├─build.php             自动生成定义文件（参考）
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
~~~


## 使用许可

nbnbk是基于ThinkPHP5的开源系统，它完全免费，可以自由的进行二次开发。


## Bug及建议

如有Bug欢迎开Issues或者邮箱 277023115@qq.com 留言，如有好的建议以及意见也欢迎交流。

