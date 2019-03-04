PHP SDK rev 1.0.1 

2011-09-05

==== 基本要求 ====

1. PHP 5.x版本（php 4.x版本兼容性未测试，如有需要，请自行修改）

2. PHP 的 mbstring 或者 iconv 模块

3. 如果需要后台交易和查询请求，必须有curl模块

Ubuntu:
    sudo apt-get install php5_curl php5_mbstring

Windows: 修改php.ini，去掉以下配置行首的分号
    ;extension=php_curl.dll 
    ;extension=php_mbstring.dll 

修改完后记得重新启动Web Server(apache/nginx/ligttpd/iis)

注：可通过 <?php phpinfo(); ?> 来查看是否有对应的模块


==== 使用说明 ====

0. 请详读《中国银联UPOP系统商户接入接口规范》和《中国银联在线支付业务机构（商户）技术改造指南》

1. 根据自己的代码选择GBK或者UTF-8编码的SDK版本。
   其他编码请自行转换，并修改 quickpay_conf.php 里面的编码配置。

2. 修改quickpay_conf.php里面的对应参数，主要包括

    security_key (商户、收单机构都需要填写)

    merId (商户填写)
    acqCode (收单机构填写, 商户留空)
    merAbbr (商户填写，收单机构可通过请求参数另外指定)

    需要根据测试、预上线、线上环境，切换api对应的url

3. 参考 front.php/back.php/query.php 来完成前台交易(消费、预授权)、后台接口调用(消费撤销、退货，预授权后续处理)、交易查询, 参考 front_notify.php/back_notify.php 来完成前台/后台通知处理。当（且仅当）用户支付成功时，我们的服务器会发起后台通知（测试环境无DNS服务，backEndUrl中请使用 IP 取代域名进行测试），可用于更新交易状态；支付成功页面会显示“返回商户”按钮，当用户点击，或者是30s后，页面会转到 请求参数frontEndUrl 指定的地址，让用户知道交易已经成功。

4. 请先在测试环境使用默认商户ID和密钥测试通过，然后联系我们的业务人员，在预上线环境配置好线上的商户ID和密钥，在预上线环境也测试通过后再切换至线上环境。
    测试环境可用的卡信息为：
        卡号 6212341111111111111
        密码 任意6位数
        短信验证码 任意6位数

5. 如果出现问题，联系我们时请详细描述出现的问题，并附上请求参数。

