<?php
// +----------------------------------------------------------------------
// | 短信服务
// +----------------------------------------------------------------------
namespace app\common\lib;

class Sms
{
    private $config = [];

    //错误信息
    private $error = '';

    public function __construct($_config=[]){
        $this->config=config('site.sms');
        if($_config){
            $this->config=array_merge($this->config,$_config);
        }
    }

    public function getSmsError(){
        return $this->error;
    }

    /**
     * 您于{$send_time}绑定手机号，验证码是：{$verify_code}。【{$site_name}】
     * 0 提交成功
     * 30：密码错误
     * 40：账号不存在
     * 41：余额不足
     * 42：帐号过期
     * 43：IP地址限制
     * 50：内容含有敏感词
     * 51：手机号码不正确
     * http://smsbao.com/
     * return bool
     */
    public function send($_mobile,$_content){
        $smsUser = $this->config['smsUser'];
        $smsPwd = urlencode($this->config['smsPwd']);
        if(!$this->config['isSms'] || !$smsUser || !$smsPwd){
            $this->error='请先配制短信应用';
            return false;
        }
        if(!$_mobile || !$_content){
            $this->error='请输入要发送的手机号码和内容';
            return false;
        }
        if(is_array($_mobile)){
            $_mobile = implode(",",$_mobile);
        }
        $mobile=urlencode($_mobile);
        $content=urlencode($_content);
        $smsPwd =md5($smsPwd);
        $url="http://api.smsbao.com/sms?u=".$smsUser."&p=".$smsPwd."&m=".$mobile."&c=".$content."";
        $result = file_get_contents($url);
        if($result=='0')
        {
            return true;
        }else{
            $this->error=$result;
            return false;
        }

    }
}