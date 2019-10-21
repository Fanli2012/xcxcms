<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\UserLogic;
use app\common\model\User as UserModel;
use app\common\lib\wechat\WechatAuth;
use app\common\lib\Image;
use app\common\lib\FileHandle;

class User extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new UserLogic();
    }
    
    //我的分销团队
    public function myteam()
	{
        //参数
        $limit = input('limit/d', 10);
        $offset = input('offset/d', 0);
        $where = array();
		$where['parent_id'] = $this->login_info['id'];
        if(input('sex', '') !== ''){$where['sex'] = input('sex');}
        if(input('group_id', '') !== ''){$where['group_id'] = input('group_id');}
		if(input('status', '') === ''){$where['status'] = UserModel::USER_STATUS_NORMAL;}else{if(input('status') != -1){$where['status'] = input('status');}}
        $orderby = input('orderby','id desc');
        if($orderby=='rand()'){$orderby = array('orderRaw','rand()');}
        
        $res = $this->getLogic()->getList($where,$orderby,'parent_id,mobile,nickname,user_name,head_img,sex,commission,consumption_money,user_rank,status,add_time',$offset,$limit);
        if($res['count']>0)
        {
            foreach($res['list'] as $k=>$v)
            {
                if(!empty($v['head_img'])){$res['list'][$k]['head_img'] = (substr($v['head_img'], 0, strlen('http')) === 'http') ? $v['head_img'] : sysconfig('CMS_SITE_CDN_ADDRESS').$v['head_img'];}
            }
        }
        
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
    
    //详情
    public function detail()
	{
        //参数
        $where['id'] = $this->login_info['id'];
        
		$res = $this->getLogic()->getUserInfo($where);
        if(!$res){Util::echo_json(ReturnData::create(ReturnData::RECORD_NOT_EXIST));}
        
		if($res['head_img']){ $res['head_img'] = (substr($res['head_img'], 0, strlen('http')) === 'http') ? $res['head_img'] : sysconfig('CMS_SITE_CDN_ADDRESS').$res['head_img']; }
		
		Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
    
    //修改
    public function edit()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));}
            $where['id'] = input('id');
            unset($_POST['id']);
			$where['user_id'] = $this->login_info['id'];
            $res = $this->getLogic()->edit($_POST,$where);
            
            Util::echo_json($res);
        }
    }
    
    //用户信息修改，仅能修改一些不敏感的信息
    public function user_info_update()
    {
        if(Helper::isPostRequest())
        {
            $where['id'] = $this->login_info['id'];
            
            $data = array();
            if(input('user_name', '')!==''){$data['user_name'] = input('user_name');}
            if(input('email', '')!==''){$data['email'] = input('email');}
            if(input('sex', '')!==''){$data['sex'] = input('sex');}
            if(input('birthday', '')!==''){$data['birthday'] = input('birthday');}
            if(input('address_id', '')!==''){$data['address_id'] = input('address_id');}
            if(input('nickname', '')!==''){$data['nickname'] = input('nickname');}
            if(input('group_id', '')!==''){$data['group_id'] = input('group_id');}
            if(input('head_img', '')!==''){$data['head_img'] = input('head_img');}
            if(input('refund_account', '')!==''){$data['refund_account'] = input('refund_account');}
            if(input('refund_name', '')!==''){$data['refund_name'] = input('refund_name');}
            
			$res = $this->getLogic()->userInfoUpdate($data, $where);
            Util::echo_json($res);
        }
    }
    
    //修改用户密码
    public function user_password_update()
    {
        $data['password'] = input('password', '');
		$data['old_password'] = input('old_password', '');
		if($data['password'] == $data['old_password']){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR, null, '新旧密码相同'));}
        
        $res = $this->getLogic()->userPasswordUpdate($data, array('id' => $this->login_info['id']));
		Util::echo_json($res);
    }
    
    //修改用户支付密码
    public function user_pay_password_update()
    {
        $data['pay_password'] = input('pay_password', '');
		$data['old_pay_password'] = input('old_pay_password', '');
		
		if($data['pay_password'] == $data['old_pay_password']){Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR, null, '新旧支付密码相同'));}
        
        $res = $this->getLogic()->userPayPasswordUpdate($data, array('id' => $this->login_info['id']));
		Util::echo_json($res);
    }
    
    //签到
	public function signin()
	{
		$res = $this->getLogic()->signin(array('id'=>$this->login_info['id']));
		Util::echo_json($res);
    }
    
    //用户推介赚钱-小程序二维码
    public function referral_qrcode()
	{
        //参数
		$public_path = $_SERVER['DOCUMENT_ROOT']; //网站根目录
        $data['scene'] = input('scene','');
        $data['page'] = input('page','');
        $data['width'] = input('width',430);
        $data['type'] = input('type', 0); //0路径存储，1base64
        $is_add_avatar = input('is_add_avatar', 0);
		
        $image_path = '/uploads/wxacode/'.md5($data['page'].$data['scene'].$data['width']).'.jpg';
		
        if($data['type']==0)
        {
            $data['image_path'] = $public_path.$image_path;
			//如果图片存在，直接返回
			if(FileHandle::check_file_exists($data['image_path']))
			{
				$res = sysconfig('CMS_SITE_CDN_ADDRESS').$image_path;
				Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
			}
        }
        
        $xcx = new WechatAuth(sysconfig('CMS_WX_MINIPROGRAM_APPID'), sysconfig('CMS_WX_MINIPROGRAM_APPSECRET'));
        $res = $xcx->getwxacodeunlimit($data);
        
        if($data['type']==0)
        {
            $res = sysconfig('CMS_SITE_CDN_ADDRESS').$image_path;
			
            $headurl = model('User')->getValue(['id'=>$this->login_info['id']], 'head_img'); //获取用户头像图片地址
			if($headurl && $is_add_avatar)
            {
				$remote_headurl = $headurl;
				//判断是否是本地文件
				if(strtolower(substr($headurl, 0, 4))!='http')
				{
					$headurl = $public_path.$headurl;
					$remote_headurl = sysconfig('CMS_SITE_CDN_ADDRESS').$remote_headurl;
				}
				
				// php保存远程用户头像到本地
				$new_head_img = $public_path.'/uploads/wxacode/head_img_' . $this->login_info['id'] .'.jpeg';
				$this->download_img($remote_headurl, $new_head_img);
				$headurl = $new_head_img;
				
				// 生成缩略图
				$image = \think\Image::open('./uploads/wxacode/head_img_' . $this->login_info['id'] .'.jpeg');
				// 按照原图的比例生成一个最大为200*200的缩略图
				$image->crop(200, 200)->save('./uploads/wxacode/head_img_' . $this->login_info['id'] .'.jpeg');
				
				//头像存在
				if(FileHandle::check_file_exists($headurl))
				{
					//编辑已保存的原头像，保存成圆形（其实不是圆形，改变它的边角为透明）
					//header("content-type:image/png"); //传入保存后的头像文件名
					$imgg = Image::yuan_img($headurl);
					$head_image_path = $public_path.'/uploads/wxacode/head_img_'.$this->login_info['id'].'.png';
					imagepng($imgg, $head_image_path);
					imagedestroy($imgg);
					
					//缩小头像（原图为200，430的小程序码logo为192）
					$target_im = imagecreatetruecolor(192,192); //创建一个新的画布（缩放后的），从左上角开始填充透明背景
					imagesavealpha($target_im, true);
					$trans_colour = imagecolorallocatealpha($target_im, 255, 255, 255, 127);
					imagefill($target_im, 0, 0, $trans_colour);
					imagefilledellipse($target_im, 96, 96, 192, 192, imagecolorallocatealpha($target_im, 255, 255, 255, 0));
					
					$o_image = imagecreatefrompng($head_image_path); //获取上文已保存的修改之后头像的内容
					imagecopyresampled($target_im,$o_image, 0, 0, 0, 0, 192, 192, 200, 200);
					$comp_path = $head_image_path;
					imagepng($target_im, $comp_path);
					imagedestroy($target_im);
					
					//传入保存后的二维码地址  
					$url = Image::create_pic_watermark($public_path.$image_path, $comp_path, 'center');
					unlink($head_image_path);
					if(isset($new_head_img)){unlink($new_head_img);}
				}
			}
        }
        else
        {
			
        }

        Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }
	
	/**
     * 文件下载
     * @param  [type] $url [下载链接包含协议]
     * @param  [type] $absolute_path [本地绝对路径包含扩展名]
     * @return [type] [description]
     */
    public function download_img($url, $path)
	{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        $resource = fopen($path, 'a');
        fwrite($resource, $file);
        fclose($resource);
    }
	
    //修改密码
    public function change_password()
    {
        $mobile = input('mobile', null);
        $password = input('password', null); //新密码
		$oldPassword = input('oldPassword', null); //旧密码
		
		if (!$mobile || !$password || !$oldPassword)
		{
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }
		
		if($password == $oldPassword)
		{
			return ReturnData::create(ReturnData::PARAMS_ERROR,'新旧密码相同');
		}
		
		if (!Helper::isValidMobile($mobile))
		{
			return ReturnData::create(ReturnData::MOBILE_FORMAT_FAIL);
		}
		
		$user = MallDataManager::userFirst(['mobile'=>$mobile,'password'=>$oldPassword,'id'=>$this->login_info['id']]);
		
		if(!$user)
		{
			return ReturnData::create(ReturnData::PARAMS_ERROR,'手机或密码错误');
		}
		
		DB::table('user')->where(['mobile'=>$mobile,'password'=>$oldPassword,'id'=>$this->login_info['id']])->update(['password'=>$password]);
		
		MallDataManager::tokenDelete(['uid'=>$this->login_info['id']]);
		
		return ReturnData::create(ReturnData::SUCCESS);
    }
	
	//找回密码，不用输入旧密码
    public function find_password()
    {
        $mobile = input('mobile', null);
        $password = input('password', null);
		
        if ($mobile && $password)
		{
            if (!Helper::isValidMobile($mobile))
			{
                return response(ReturnData::create(ReturnData::MOBILE_FORMAT_FAIL));
            }
			
            //判断验证码是否有效
            $code = input('code', '');
            $type = input('type', null);
            if($type != VerifyCode::TYPE_CHANGE_PASSWORD)
                return response(ReturnData::create(ReturnData::INVALID_VERIFY_CODE,'验证码类型错误'));
            $verifyCode = VerifyCode::isVerify($mobile, $code, $type);
			
            if($verifyCode)
            {
                try
				{
                    DB::beginTransaction();
                    $verifyCode->status = VerifyCode::STATUS_USE;
                    $verifyCode->save();
					
                    if ($user = MallDataManager::userFirst(['mobile'=>$mobile]))
					{
                        DB::table('user')->where(['mobile'=>$mobile])->update(['password'=>$password]);
                        
						MallDataManager::tokenDelete(['uid'=>$user->id]);
						
						$response = response(ReturnData::create(ReturnData::SUCCESS));
                    }
					else
					{
                        $response = response(ReturnData::create(ReturnData::PARAMS_ERROR));
                    }
					
					DB::commit();
					
                    return $response;
                }
				catch (Exception $e)
				{
                    DB::rollBack();
                    return response(ReturnData::error($e->getCode(), $e->getMessage()));
                }
            }
            else
            {
                return response(ReturnData::create(ReturnData::INVALID_VERIFY_CODE));
            }
        }
		else
		{
            return response(ReturnData::create(ReturnData::PARAMS_ERROR));
        }
    }
	
	/**
     * 修改手机号
     * @param string $_POST['mobile'] 新手机号码
	 * @param string $_POST['code'] 新手机验证码
     * @return array
     */
    public function change_mobile()
    {
        $mobile = input('mobile', null); //新手机号码
        $code = input('code', null); //新手机验证码
        
        if(Helper::isPostRequest())
        {
            $where['id'] = $this->login_info['id'];
            $res = $this->getLogic()->changeMobile($_POST, $where);
            Util::echo_json($res);
        }
    }
    
	/**
     * 微信小程序获取用户手机号码
     * @param string $_POST['code'] 用户登录凭证（有效期五分钟）。开发者需要在开发者服务器后台调用 auth.code2Session，使用 code 换取 openid 和 session_key 等信息
	 * @param string $_POST['encryptedData'] 包括敏感数据在内的完整用户信息的加密数据
	 * @param string $_POST['iv'] 加密算法的初始向量
     * @return array
     */
    public function bind_wechat_miniprogram_user_mobile()
    {
        //参数
        /* $code = input('code', '');
        $iv = input('iv', '');
        $code = input('encryptedData', ''); */
        // 获取小程序用户手机号
        $res = $this->getLogic()->getWechatUserMobile(request()->param());
        if($res['code'] != ReturnData::SUCCESS){Util::echo_json($res);}
        
        // 判断手机号是否存在
        $user = model('User')->getOne(array('mobile'=>$res['data']['purePhoneNumber'], 'id'=>array('<>',$this->login_info['id'])));
        if($user)
        {
            Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR, null, '该手机号已存在'));
        }
        
        // 修改用户手机号
        $where_user['id'] = $this->login_info['id'];
        model('User')->edit(array('mobile'=>$res['data']['purePhoneNumber']), $where_user);
        
        Util::echo_json($res);
    }
}