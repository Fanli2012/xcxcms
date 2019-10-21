# xcxcms
微信小程序cms

## 访问

1、微信小程序搜索"繁橙工作室"

2、扫码访问

![alt text](miniprogram/images/xcxewm.jpg "繁橙工作室")


## 预览

![alt text](miniprogram/images/screenshots-wap.gif "截图")


## 目录结构

1、miniprogram，小程序目录

2、server，小程序后端目录，基于ThinkPHP5


## 小程序后端说明

1、基于ThinkPHP5，初始绑定的域名是www.nbnbk3.com，自行修改成自己要绑定的域名，以下说明如果遇到www.nbnbk3.com请修改成自己的域名

2、PHP+Mysql

3、后台登录：http://www.nbnbk3.com/fladmin/login，账号：admin888，密码：123456

4、恢复后台默认账号密码：http://www.nbnbk3.com/fladmin/login/recoverpwd


## 后端安装

跟thinkphp5安装一样，只是多了一步数据库导入

1、 导入数据库

1) 打开根目录下的nbnbk.sql文件，替换文件里面的 http://www.nbnbk3.com 为自己的站点根网址，格式：http(s)://+域名

2) 导入数据库，导入完成之后，因为数据表有索引，需要修复表不然很容易报错

2、 修改数据库连接参数

打开/application/database.php文件,修改相关配置

3、 根目录执行 Composer install 或 Composer update 示例：php composer.phar install 或 php composer.phar update

4、 域名绑定到server/public目录

5、 登录后台->系统设置->系统配置参数，更新配置：http://www.nbnbk3.com/fladmin/index/upcache

Linux系统文件/目录权限

 + extend/wxJsSdk目录设置成可读写777
 + public/index.php文件设置成只读444

6、 打开miniprogram目录下的config.js文件，修改【appApiUrl: "http://www.nbnbk3.com/api"】为【appApiUrl: "http(s)://+域名/api"】，这个是为小程序提供数据的api接口路径


## 注意

后端站点只能放在根目录

