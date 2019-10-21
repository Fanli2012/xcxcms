<?php

namespace app\common\lib;

use app\common\lib\ReturnData;

class Token
{
    const TYPE_APP = 0;
    const TYPE_ADMIN = 1;
    const TYPE_WEIXIN = 2;
    const TYPE_WAP = 3;
    const TYPE_PC = 4;

    // 已验证的type
    public static $type;
    // 验证为token时的uid
    public static $uid;
    // 验证为sign时的app.id
    public static $app;
    // 已验证的data
    public static $data = array();

    /**
     * 验证token
     *
     * @param $token
     *
     * @return bool
     */
    public static function checkToken($token)
    {
        $token = db('token')->where(array('token' => $token))->find();

        if ($token) {
            self::$type = $token['type'];
            self::$uid = $token['uid'];
            self::$data = $token['data'] ? json_decode($token['data'], true) : array();
        }

        return $token ? true : false;
    }

    /**
     * 验证sign，
     * sign生成方式：md5(app_key + app_secret + time)
     * 必传参数：app_key, sign, sign_time
     *
     * @param $appKey
     * @param $signTime
     * @param $sign
     *
     * @return bool
     */
    public static function checkSign($appKey, $signTime, $sign)
    {
        if (!$appRes = db('appsign')->where('app_key', $appKey)->find()) {
            return false;
        }

        //验证sign
        $newSign = md5($appKey . $appRes['app_secret'] . $signTime);
        if ($sign == $newSign) {
            self::$type = self::TYPE_ADMIN;
            self::$app = $appRes;
            return true;
        }

        return false;
    }

    /**
     * 生成token
     *
     * @param $type
     * @param $uid
     * @param $data
     *
     * @return string
     */
    public static function getToken($type, $uid, $data = array())
    {
        //支持多账号登录
        if ($token = db('token')->where(array('type' => $type, 'uid' => $uid))->order('id desc')->find()) {
            if ($data == $token['data'] && strtotime($token['expired_at']) > time()) {
                return array('access_token' => $token['token'], 'expired_at' => $token['expired_at']);
            }
        }

        //生成新token
        $token = md5($type . '-' . $uid . '-' . microtime() . rand(0, 9999));
        $expired_at = date("Y-m-d H:i:s", (time() + 3600 * 24 * 30)); //token 30天过期

        db('token')->insert(array(
            'token' => $token,
            'type' => $type,
            'uid' => $uid,
            'data' => $data ? json_encode($data) : '',
            'expired_at' => $expired_at
        ));

        return array('access_token' => $token, 'expired_at' => $expired_at, 'uid' => $uid, 'type' => $type);
    }

    /**
     * Token验证
     * token可以在header里面传递【Token】，也可以在参数里面传【token】，注意区分大小写
     */
    public static function TokenAuth($request)
    {
        $token = $request->header('AccessToken') ?: $request->param('access_token');

        if ($token == '') {
            exit(json_encode(ReturnData::create(ReturnData::FORBIDDEN)));
        }

        if (!Token::checkToken($token)) {
            exit(json_encode(ReturnData::create(ReturnData::TOKEN_ERROR)));
        }
    }
}