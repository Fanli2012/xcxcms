<?php
namespace app\miniprogram\controller;

use think\Request;
use think\Db;
use think\Session;
use think\Cache;
use app\common\lib\WxComponent;

class Login extends Common
{
    //登录
    public function index()
	{
        $component_verify_ticket = json_decode(db('sysconfig')->where(['varname'=>'CMS_WX_COMPONENT_VERIFY_TICKET'])->value('value'),true);
        $WxComponent = new WxComponent(sysconfig('CMS_WX_COMPONENT_APPID'), sysconfig('CMS_WX_COMPONENT_APPSECRET'), $component_verify_ticket['ComponentVerifyTicket'], sysconfig('CMS_WX_ENCODINGAESKEY'), sysconfig('CMS_WX_TOKEN'));
        
        $api_component_token = Cache::get('component_access_token','');
        if(!$api_component_token)
        {
            $api_component_token = $WxComponent->getAccessToken();
            Cache::set('component_access_token', $api_component_token['component_access_token'], 3600);
        }
        
        $pre_auth_code = $WxComponent->getPreauthCode(Cache::get('component_access_token'));
        
        $login_url = "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=".sysconfig('CMS_WX_COMPONENT_APPID')."&pre_auth_code=$pre_auth_code".."&redirect_uri=".url('authcallback');
        $this->assign('login_url',$login_url);
        
        return $this->fetch();
    }
    
    public function authCallback()
	{
        exit;
    }
    
    //接收微信推送的component_verify_ticket
    public function componentVerifyTicket()
	{
        if (!class_exists("WXBizMsgCrypt"))
        {
            include_once APP_PATH.'common/lib/aes/wxBizMsgCrypt.php';
        }
        
        if($_SERVER['REQUEST_METHOD'] == "POST")
        {
            $dec_msg = "";
            
            $postStr = file_get_contents("php://input");
            if(!$postStr)
            {
                $postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
            }
            
            if(!$postStr)
            {
                exit;
            }
            
            $pc  = new \WXBizMsgCrypt(sysconfig('CMS_WX_TOKEN'), sysconfig('CMS_WX_ENCODINGAESKEY'), sysconfig('CMS_WX_COMPONENT_APPID'));
            $ret = $pc->decryptMsg($_GET['msg_signature'], $_GET['timestamp'], $_GET['nonce'], $postStr, $dec_msg);
            if($ret === 0)
            {
                $arr = (array) simplexml_load_string($dec_msg, 'SimpleXMLElement', LIBXML_NOCDATA);
                
                if($arr)
                {
                    if(db('sysconfig')->where(['varname'=>'CMS_WX_COMPONENT_VERIFY_TICKET'])->update(json_encode($arr)))
                    {
                        exit('success');
                    }
                }
                
                exit;
            }
            else
            {
                exit;
            }
        }
        else
        {
            exit;
        }
    }
}