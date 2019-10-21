<?php
// +----------------------------------------------------------------------
// | 极光消息推送
// +----------------------------------------------------------------------
namespace app\common\service;

class JPush
{
    private $appkey;
    private $secret;

    public function __construct()
    {
        $this->appkey = 'a8976f94ae419d6a9df52a86';
        $this->secret = '3a6cf8cb216a60cdd1bead65';
    }

    /*
    * 消息推送
    * @param uid 推送用户
    * @param content 推送内容
    */
    public final function send($uid = '', $content = '您有条新订单待处理', $ext = [])
    {
        if (empty($uid)) {
            return false;
        }

        $client = new \JPush\Client($this->appkey, $this->secret);
        $pusher = $client->push();
        $pusher->setPlatform('all');
        //$pusher->addAllAudience();
        $pusher->addAlias($uid);
        $pusher->setNotificationAlert($content);

        if ($ext) {
            $pusher->message('msg', ['extras' => $ext]);
        }

        try {
            $pusher->options(['apns_production' => false])->send();
        } catch (\JPush\Exceptions\JPushException $e) {
            //$this->error($e);
        }

        return true;
    }
}