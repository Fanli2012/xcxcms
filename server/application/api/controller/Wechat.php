<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\lib\wechat\WechatAuth;

class Wechat extends UserBase
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    // 以code换取 用户唯一标识openid 和 会话密钥session_key
    public function miniprogramWxlogin()
	{
        include_once APP_PATH.'common/lib/wechat/aes/wxBizDataCrypt.php';
        /**
         * 3.小程序调用server获取token接口, 传入code, rawData, signature, encryptData.
         */
        $where = array();
        $code = input('code', null);
        $rawData = input('rawData', null);
        $signature = input('signature', null);
        $encryptedData = input('encryptedData', null);
        $iv = input('iv', null);
        
        /**
         * 4.server调用微信提供的jsoncode2session接口获取openid, session_key, 调用失败应给予客户端反馈
         * , 微信侧返回错误则可判断为恶意请求, 可以不返回. 微信文档链接
         * 这是一个 HTTP 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。其中 session_key 是对用户数据进行加密签名的密钥。
         * 为了自身应用安全，session_key 不应该在网络上传输。
         * 接口地址："https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code"
         */
        if($code == null){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $wechat = new WechatAuth(sysconfig('CMS_WX_MINIPROGRAM_APPID'), sysconfig('CMS_WX_MINIPROGRAM_APPSECRET'));
        $res = $wechat->miniprogram_wxlogin($code);
        if (!isset($res['session_key'])) {
            exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR, null, 'requestTokenFailed')));
        }
        
        $session_key = $res['session_key'];
        
        /**
         * 5.server计算signature, 并与小程序传入的signature比较, 校验signature的合法性, 不匹配则返回signature不匹配的错误. 不匹配的场景可判断为恶意请求, 可以不返回.
         * 通过调用接口（如 wx.getUserInfo）获取敏感数据时，接口会同时返回 rawData、signature，其中 signature = sha1( rawData + session_key )
         *
         * 将 signature、rawData、以及用户登录态发送给开发者服务器，开发者在数据库中找到该用户对应的 session-key
         * ，使用相同的算法计算出签名 signature2 ，比对 signature 与 signature2 即可校验数据的可信度。
         */
        $signature2 = sha1($rawData . $session_key);
        if ($signature2 != $signature) exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR, null, 'signNotMatch')));
        
        /**
         *
         * 6.使用第4步返回的session_key解密encryptData, 将解得的信息与rawData中信息进行比较, 需要完全匹配,
         * 解得的信息中也包括openid, 也需要与第4步返回的openid匹配. 解密失败或不匹配应该返回客户相应错误.
         * （使用官方提供的方法即可）
         */
        $pc = new \WXBizDataCrypt(sysconfig('CMS_WX_MINIPROGRAM_APPID'), $session_key);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        
        if ($errCode != 0)
        {
            exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR, null, 'encryptDataNotMatch')));
        }
        
        /**
         * 7.生成第三方3rd_session，用于第三方服务器和小程序之间做登录态校验。为了保证安全性，3rd_session应该满足：
         * a.长度足够长。建议有2^128种组合，即长度为16B
         * b.避免使用srand（当前时间）然后rand()的方法，而是采用操作系统提供的真正随机数机制，比如Linux下面读取/dev/urandom设备
         * c.设置一定有效时间，对于过期的3rd_session视为不合法
         *
         * 以 $session3rd 为key，sessionKey+openId为value，写入memcached
         */
        $data = json_decode($data, true);
        /* $session3rd = randomFromDev(16);
        $data['session3rd'] = $session3rd;
        cache($session3rd, $data['openId'] . $session_key); */
        
        $add_data['password'] = md5('123456');
        $add_data['status'] = 1;
        $add_data['source'] = 5;
        $add_data['head_img'] = isset($data['avatarUrl'])?$data['avatarUrl']:'';
        $add_data['sex'] = isset($data['gender'])?$data['gender']:0;
        $add_data['nickname'] = isset($data['nickName']) ? $this->filterEmoji($data['nickName']) : '';
        $add_data['openid'] = isset($data['openId'])?$data['openId']:'';
        $add_res = logic('User')->xcxadd($add_data);
        if($add_res['code'] != ReturnData::SUCCESS)
        {
            exit(json_encode(ReturnData::create($add_res['code'], null, $add_res['msg'])));
        }
        
        //生成token
        $data['token'] = logic('UserToken')->getToken($add_res['data']['id']);
        
        $data['id'] = $add_res['data']['id'];
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS, $data)));
    }
    
    /**
	 * 过滤emoji
	 */
	public function filterEmoji($str)
	{
        // preg_replace_callback执行一个正则表达式搜索并且使用一个回调进行替换
		$str = preg_replace_callback('/./u', function (array $match) {
                    return strlen($match[0]) >= 4 ? '' : $match[0];
                }, $str);
        
		return $str;
	}
}