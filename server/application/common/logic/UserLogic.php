<?php

namespace app\common\logic;

use think\Loader;
use think\Validate;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\model\User;
use app\common\model\Token;
use app\common\model\VerifyCode;
use app\common\lib\wechat\WechatAuth;
use app\common\lib\Validator;

class UserLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new User();
    }

    public function getValidate()
    {
        return Loader::validate('User');
    }

    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = '', $limit = '')
    {
        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {
                //$res['list'][$k] = $this->getDataView($v);
                $res['list'][$k] = $res['list'][$k]->append(array('status_text', 'sex_text', 'user_rank_text'))->toArray();
            }
        }

        return $res;
    }

    //分页html
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getPaginate($where, $order, $field, $limit);

        $res = $res->each(function ($item, $key) {
            //$item = $this->getDataView($item);
            return $item;
        });

        return $res;
    }

    //全部列表
    public function getAll($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getAll($where, $order, $field, $limit);

        /* if($res)
        {
            foreach($res as $k=>$v)
            {
                //$res[$k] = $this->getDataView($v);
            }
        } */

        return $res;
    }

    //详情
    public function getOne($where = array(), $field = '*')
    {
        $res = $this->getModel()->getOne($where, $field);
        if (!$res) {
            return false;
        }

        //$res = $this->getDataView($res);
        $res = $res->append(array('status_text', 'sex_text', 'user_rank_text'))->toArray();

        return $res;
    }

    //添加
    public function add($data = array(), $type = 0)
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        //添加时间、更新时间
        $time = time();
        if (!(isset($data['add_time']) && !empty($data['add_time']))) {
            $data['add_time'] = $time;
        }
        if (!(isset($data['update_time']) && !empty($data['update_time']))) {
            $data['update_time'] = $time;
        }

        $check = $this->getValidate()->scene('add')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        $res = $this->getModel()->add($data, $type);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    //修改
    public function edit($data, $where = array())
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::SUCCESS);
        }

        //更新时间
        $time = time();
        if (!(isset($data['update_time']) && !empty($data['update_time']))) {
            $data['update_time'] = $time;
        }

        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    //删除
    public function del($where)
    {
        if (empty($where)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        $check = $this->getValidate()->scene('del')->check($where);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        $res = $this->getModel()->del($where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    /**
     * 数据获取器
     * @param array $data 要转化的数据
     * @return array
     */
    private function getDataView($data = array())
    {
        return getDataAttr($this->getModel(), $data);
    }

    //获取用户详情
    public function getUserInfo($where)
    {
        $user = $this->getModel()->getOne($where);
        if (!$user) {
            return false;
        }

        if ($user['pay_password']) {
            $user['pay_password'] = 1;
        } else {
            $user['pay_password'] = 0;
        }
        unset($user['password']);

        $user['reciever_address'] = model('UserAddress')->getOne(array('id' => $user['address_id']));
        $user['collect_goods_count'] = model('UserGoodsCollect')->getCount(array('user_id' => $user['id']));
        $user['bonus_count'] = model('UserBonus')->getCount(array('user_id' => $user['id'], 'status' => 0));

        $user = $user->append(array('status_text', 'sex_text', 'user_rank_text'))->toArray();

        return $user;
    }

    /**
     * 用户名/手机号/邮箱+密码登录
     * @param string $data ['user_name'] 用户名
     * @param string $data ['password'] 密码
     * @param string $data ['from'] 来源：0app,1admin,2weixin,3wap,4pc,5miniprogram
     * @return array
     */
    public function login($data)
    {
        //验证数据
        $validate = new Validate([
            ['user_name', 'require|max:30', '账号不能为空|账号不能超过30个字符'],
            ['password', 'require|length:6,18', '密码不能为空|密码6-18位']
        ]);
        if (!$validate->check($data)) {
            return ReturnData::create(ReturnData::FAIL, null, $validate->getError());
        }

        $user_name = $data['user_name'];
        $password = $this->passwordEncrypt($data['password']);
        //用户名/手机号/邮箱+密码
        $user = $this->getModel()->getDb()->where(function ($query) use ($user_name, $password) {
            $query->where('user_name', $user_name)->where('password', $password)->where('delete_time', User::USER_UNDELETE);
        })->whereOr(function ($query) use ($user_name, $password) {
            $query->where('email', $user_name)->where('password', $password)->where('delete_time', User::USER_UNDELETE);
        })->whereOr(function ($query) use ($user_name, $password) {
            $query->where('mobile', $user_name)->where('password', $password)->where('delete_time', User::USER_UNDELETE);
        })->find();
        if (!$user) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '登录名或密码错误');
        }

        //更新登录时间
        $this->getModel()->edit(['login_time' => time()], ['id' => $user['id']]);

        //获取用户信息
        $user_info = $this->getUserInfo(['id' => $user['id']]);

        if (isset($data['from']) && $data['from'] != '') {
            //生成Token
            $token = logic('Token')->getToken($user_info['id'], $data['from']);
            if (!$token) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, 'Token生成失败');
            }
            $user_info['token'] = $token;
        }

        return ReturnData::create(ReturnData::SUCCESS, $user_info, '登录成功');
    }

    /**
     * 微信登录
     * @param string $data ['openid'] 微信openid
     * @param string $data ['unionid'] 微信unionid
     * @param int $data ['sex'] 性别
     * @param string $data ['head_img'] 头像
     * @param string $data ['nickname'] 昵称
     * @param int $data ['parent_id'] 推荐人ID
     * @param string $data ['parent_mobile'] 推荐人手机号
     * @return array
     */
    public function wxLogin($data)
    {
        $edit_user = array();
        $user = $this->getModel()->getOne(array('openid' => $data['openid']));
        if (!$user) {
            $data['add_time'] = $data['update_time'] = time();

            //默认用户名
            if (!(isset($data['user_name']) && !empty($data['user_name']))) {
                $data['user_name'] = date('YmdHis') . rand(1000, 9999);
            }

            //默认密码123456
            /* if(!(isset($data['password']) && !empty($data['password'])))
            {
                $data['password'] = $this->passwordEncrypt('123456');
            } */

            $check = $this->getValidate()->scene('wx_register')->check($data);
            if (!$check) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
            }

            //昵称过滤Emoji
            if (isset($data['nickname']) && !empty($data['nickname'])) {
                $data['nickname'] = Helper::filterEmoji($data['nickname']);
            }

            //判断推荐人是否存在
            if (isset($data['parent_id']) && $data['parent_id'] > 0) {
                $parent_user = $this->getModel()->getOne(array('id' => $data['parent_id']));
                if (!$parent_user) {
                    return ReturnData::create(ReturnData::PARAMS_ERROR, null, '推荐人不存在');
                }
            }

            //判断推荐人手机号
            if (isset($data['parent_mobile']) && $data['parent_mobile'] != '') {
                if (Validator::isMobile($data['parent_mobile'])) {
                    return ReturnData::create(ReturnData::PARAMS_ERROR, null, '推荐人手机号码错误');
                }
                $parent_user = $this->getModel()->getOne(array('mobile' => $data['parent_mobile']));
                if (!$parent_user) {
                    return ReturnData::create(ReturnData::PARAMS_ERROR, null, '推荐人不存在或推荐人手机号错误');
                }

                $data['parent_id'] = $parent_user['id'];
            }

            //判断用户名
            if (isset($data['user_name']) && !empty($data['user_name'])) {
                if ($this->getModel()->getOne(array('user_name' => $data['user_name']))) {
                    return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户名已存在');
                }
            }

            $user_id = $this->getModel()->add($data);
            if (!$user_id) {
                return ReturnData::create(ReturnData::SYSTEM_FAIL);
            }

            //更新用户名user_name，微信登录没有用户名
            $edit_user['user_name'] = 'u' . $user_id;
            $user['id'] = $user_id;
        }

        //更新登录时间
        $edit_user['login_time'] = time();
        $this->getModel()->edit($edit_user, array('id' => $user['id']));

        //获取用户信息
        $user_info = $this->getUserInfo(['id' => $user['id']]);

        //生成Token
        $token = logic('Token')->getToken($user_info['id'], Token::TOKEN_TYPE_WEIXIN);
        $user_info['token'] = $token;

        return ReturnData::create(ReturnData::SUCCESS, $user_info, '登录成功');
    }

    /**
     * 微信小程序登录
     * @param string $data ['code'] 用户登录凭证（有效期五分钟）。开发者需要在开发者服务器后台调用 auth.code2Session，使用 code 换取 openid 和 session_key 等信息
     * @param string $data ['rawData'] 不包括敏感信息的原始数据字符串，用于计算签名
     * @param string $data ['signature'] 使用 sha1( rawData + sessionkey ) 得到字符串，用于校验用户信息
     * @param string $data ['encryptedData'] 包括敏感数据在内的完整用户信息的加密数据
     * @param string $data ['iv'] 加密算法的初始向量
     * @param int $data ['parent_id'] 推荐人ID
     * @param string $data ['parent_mobile'] 推荐人手机号
     * @return array
     */
    public function miniprogramWxlogin($data)
    {
        $miniprogram_user_info = $this->getMiniprogramLoginUserinfo($data);
        if ($miniprogram_user_info['code'] != ReturnData::SUCCESS) {
            return ReturnData::create(ReturnData::FAIL, null, $miniprogram_user_info['msg']);
        }
        $data = array_merge($data, $miniprogram_user_info['data']);
        $user = $this->getModel()->getOne(array('openid' => $data['openId']));
        if (!$user) {
            $data['add_time'] = $data['update_time'] = time();

            //默认用户名
            if (!(isset($data['user_name']) && !empty($data['user_name']))) {
                $data['user_name'] = date('YmdHis') . rand(1000, 9999);
            }

            //昵称过滤Emoji
            if (isset($data['nickName']) && !empty($data['nickName'])) {
                $data['nickname'] = Helper::filterEmoji($data['nickName']);
            }

            //头像
            if (isset($data['avatarUrl']) && !empty($data['avatarUrl'])) {
                $data['head_img'] = $data['avatarUrl'];
            }

            //性别
            $data['sex'] = 0;
            if (isset($data['gender']) && $data['gender'] > 0) {
                $data['sex'] = $data['gender'];
            }

            //openid
            $data['openid'] = 0;
            if (isset($data['openId']) && !empty($data['openId'])) {
                $data['openid'] = $data['openId'];
            }

            $check = $this->getValidate()->scene('wx_register')->check($data);
            if (!$check) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
            }

            //判断推荐人是否存在
            if (isset($data['parent_id']) && $data['parent_id'] > 0) {
                $parent_user = $this->getModel()->getOne(array('id' => $data['parent_id']));
                if (!$parent_user) {
                    return ReturnData::create(ReturnData::PARAMS_ERROR, null, '推荐人不存在');
                }
            }

            //判断推荐人手机号
            if (isset($data['parent_mobile']) && $data['parent_mobile'] != '') {
                if (Validator::isMobile($data['parent_mobile'])) {
                    return ReturnData::create(ReturnData::PARAMS_ERROR, null, '推荐人手机号码错误');
                }
                $parent_user = $this->getModel()->getOne(array('mobile' => $data['parent_mobile']));
                if (!$parent_user) {
                    return ReturnData::create(ReturnData::PARAMS_ERROR, null, '推荐人不存在或推荐人手机号错误');
                }

                $data['parent_id'] = $parent_user['id'];
            }

            //判断用户名
            if (isset($data['user_name']) && !empty($data['user_name'])) {
                if ($this->getModel()->getOne(array('user_name' => $data['user_name']))) {
                    return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户名已存在');
                }
            }

            $user_id = $this->getModel()->add($data);
            if (!$user_id) {
                return ReturnData::create(ReturnData::SYSTEM_FAIL);
            }

            //更新用户名user_name，微信登录没有用户名
            if (!model('User')->getOne(array('user_name' => 'u' . $user_id))) {
                $edit_user['user_name'] = 'u' . $user_id;
            }
            $user['id'] = $user_id;
        }

        //更新登录时间
        $edit_user['login_time'] = time();
        $this->getModel()->edit($edit_user, array('id' => $user['id']));

        //获取用户信息
        $user_info = $this->getUserInfo(array('id' => $user['id']));

        //生成Token
        $token = logic('Token')->getToken($user_info['id'], Token::TOKEN_TYPE_MINIPROGRAM);
        $user_info['token'] = $token;

        return ReturnData::create(ReturnData::SUCCESS, $user_info, '登录成功');
    }

    /**
     * 用户名+密码注册
     * @param string $data ['user_name'] 用户名
     * @param string $data ['mobile'] 手机号
     * @param string $data ['password'] 密码
     * @param int $data ['parent_id'] 推荐人ID
     * @param string $data ['parent_mobile'] 推荐人手机号
     * @return array
     */
    public function register($data)
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        $data['add_time'] = $data['update_time'] = time();

        $check = $this->getValidate()->scene('register')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        //判断推荐人是否存在
        if (isset($data['parent_id']) && $data['parent_id'] > 0) {
            $parent_user = $this->getModel()->getOne(array('id' => $data['parent_id']));
            if (!$parent_user) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '推荐人不存在');
            }
        }

        //判断推荐人手机号
        if (isset($data['parent_mobile']) && $data['parent_mobile'] != '') {
            if (Validator::isMobile($data['parent_mobile'])) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '推荐人手机号码错误');
            }
            $user = $this->getModel()->getOne(array('mobile' => $data['parent_mobile']));
            if (!$user) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '推荐人不存在');
            }

            $data['parent_id'] = $user['id'];
        }

        //判断用户名
        if (isset($data['user_name']) && $data['user_name'] != '') {
            if ($this->getModel()->getOne(array('user_name' => $data['user_name']))) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户名已存在');
            }
        }

        $data['password'] = $this->passwordEncrypt($data['password']);

        $user_id = $this->getModel()->add($data);
        if (!$user_id) {
            return ReturnData::create(ReturnData::SYSTEM_FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $user_id, '注册成功');
    }

    //用户信息修改
    public function userInfoUpdate($data, $where = array())
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::SUCCESS);
        }

        //更新时间
        if (!(isset($data['update_time']) && !empty($data['update_time']))) {
            $data['update_time'] = time();
        }

        //验证数据
        $validate = new Validate([
            ['parent_id', 'number|max:11', '推荐人ID必须是数字|推荐人ID格式不正确'],
            ['email', 'email', '邮箱格式不正确'],
            ['nickname', 'max:30', '昵称不能超过30个字符'],
            ['user_name', 'max:30|regex:/^[-_a-zA-Z0-9]{2,18}$/i', '用户名不能超过30个字符|用户名2-18个字符'],
            ['head_img', 'max:250', '头像格式不正确'],
            ['sex', 'in:0,1,2', '性别：1男2女'],
            ['birthday', 'regex:/\d{4}-\d{2}-\d{2}/', '生日格式不正确'],
            ['address_id', 'number|max:11', '收货地址ID必须是数字|收货地址ID格式不正确'],
            ['refund_account', 'max:30', '退款账户不能超过30个字符'],
            ['refund_name', 'max:20', '退款姓名不能超过20个字符'],
            ['signin_time', 'number|max:11', '签到时间格式不正确|签到时间格式不正确'],
            ['group_id', 'number|max:11', '分组ID必须是数字|分组ID格式不正确'],
        ]);
        if (!$validate->check($data)) {
            return ReturnData::create(ReturnData::FAIL, null, $validate->getError());
        }

        $record = $this->getModel()->getOne($where);
        if (!$record) {
            return ReturnData::create(ReturnData::RECORD_NOT_EXIST);
        }

        //判断用户名
        if (isset($data['user_name']) && $data['user_name'] != '') {
            $where_user_name['user_name'] = $data['user_name'];
            $where_user_name['id'] = ['<>', $record['id']]; //排除自身
            if ($this->getModel()->getOne($where_user_name)) {
                return ReturnData::create(ReturnData::FAIL, null, '该用户名已存在');
            }
        }

        //判断邮箱
        if (isset($data['email']) && $data['email'] != '') {
            $where_user_name['email'] = $data['email'];
            $where_user_name['id'] = ['<>', $record['id']]; //排除自身
            if ($this->getModel()->getOne($where_user_name)) {
                return ReturnData::create(ReturnData::FAIL, null, '该邮箱已存在');
            }
        }

        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    //修改用户密码
    public function userPasswordUpdate($data, $where = array())
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::SUCCESS);
        }

        $check = $this->getValidate()->scene('user_password_update')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        $user = $this->getModel()->getOne($where);
        if (!$user) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在');
        }

        if ($this->passwordEncrypt($data['old_password']) != $user['password']) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '旧密码错误');
        }

        $data['password'] = $this->passwordEncrypt($data['password']);
        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    //修改用户支付密码
    public function userPayPasswordUpdate($data, $where = array())
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::SUCCESS);
        }

        $check = $this->getValidate()->scene('user_pay_password_update')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        $user = $this->getModel()->getOne($where);
        if (!$user) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在');
        }

        if ($user['pay_password']) {
            if ($this->passwordEncrypt($data['old_pay_password']) != $user['pay_password']) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '旧支付密码错误');
            }
        }

        $data['pay_password'] = $this->passwordEncrypt($data['pay_password']);
        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    /**
     * 签到
     * @param string $where ['id'] 用户ID
     * @return array
     */
    public function signin($where)
    {
        $where['status'] = User::USER_STATUS_NORMAL;
        $user = $this->getModel()->getOne($where);
        if (!$user) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在');
        }

        $signin_time = '';
        if (!empty($user['signin_time'])) {
            $signin_time = date('Ymd', $user['signin_time']);
        } //签到时间

        $time = time();
        $today = date('Ymd', $time); //今日日期

        if ($signin_time == $today) {
            return ReturnData::create(ReturnData::FAIL, null, '今日已签到');
        }

        $signin_point = (int)sysconfig('CMS_SIGN_POINT'); //签到积分
        $res = logic('UserPoint')->add(array('type' => 0, 'point' => $signin_point, 'desc' => '签到', 'user_id' => $user['id'])); //添加签到积分记录，并增加用户积分
        if ($res['code'] != ReturnData::SUCCESS) {
            return ReturnData::create(ReturnData::FAIL, null, $res['msg']);
        }
        $this->getModel()->edit(array('signin_time' => $time), array('id' => $user['id'])); //更新签到时间

        return ReturnData::create(ReturnData::SUCCESS, null, '签到成功');
    }

    //密码加密
    public function passwordEncrypt($password)
    {
        if ($password == '') {
            return '';
        }
        return md5($password);
    }

    /**
     * 小程序获取用户信息，以code换取 用户唯一标识openid 和 会话密钥session_key
     * @param string $data ['code'] 用户登录凭证（有效期五分钟）。开发者需要在开发者服务器后台调用 auth.code2Session，使用 code 换取 openid 和 session_key 等信息
     * @param string $data ['rawData'] 不包括敏感信息的原始数据字符串，用于计算签名
     * @param string $data ['signature'] 使用 sha1( rawData + sessionkey ) 得到字符串，用于校验用户信息
     * @param string $data ['encryptedData'] 包括敏感数据在内的完整用户信息的加密数据
     * @param string $data ['iv'] 加密算法的初始向量
     * @return array
     */
    public function getMiniprogramLoginUserinfo($data)
    {
        include_once APP_PATH . 'common/lib/wechat/aes/wxBizDataCrypt.php';
        /**
         * 3.小程序调用server获取token接口, 传入code, rawData, signature, encryptData.
         */
        $code = $data['code'];
        $rawData = $data['rawData'];
        $signature = $data['signature'];
        $encryptedData = $data['encryptedData'];
        $iv = $data['iv'];

        /**
         * 4.server调用微信提供的jsoncode2session接口获取openid, session_key, 调用失败应给予客户端反馈
         * , 微信侧返回错误则可判断为恶意请求, 可以不返回. 微信文档链接
         * 这是一个 HTTP 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。其中 session_key 是对用户数据进行加密签名的密钥。
         * 为了自身应用安全，session_key 不应该在网络上传输。
         * 接口地址："https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code"
         */
        if ($code == null) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }
        $wechat = new WechatAuth(sysconfig('CMS_WX_MINIPROGRAM_APPID'), sysconfig('CMS_WX_MINIPROGRAM_APPSECRET'));
        $res = $wechat->miniprogram_wxlogin($code);
        if (!isset($res['session_key'])) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, 'requestTokenFailed');
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
        if ($signature2 != $signature) return ReturnData::create(ReturnData::PARAMS_ERROR, null, 'signNotMatch');

        /**
         *
         * 6.使用第4步返回的session_key解密encryptData, 将解得的信息与rawData中信息进行比较, 需要完全匹配,
         * 解得的信息中也包括openid, 也需要与第4步返回的openid匹配. 解密失败或不匹配应该返回客户相应错误.
         * （使用官方提供的方法即可）
         */
        $pc = new \WXBizDataCrypt(sysconfig('CMS_WX_MINIPROGRAM_APPID'), $session_key);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);

        if ($errCode != 0) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, 'encryptDataNotMatch');
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
        if (!$data) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '没有数据');
        }
        return ReturnData::create(ReturnData::SUCCESS, $data);

        $user_data['head_img'] = isset($data['avatarUrl']) ? $data['avatarUrl'] : '';
        $user_data['sex'] = isset($data['gender']) ? $data['gender'] : 0;
        $user_data['nickname'] = isset($data['nickName']) ? Helper::filterEmoji($data['nickName']) : '';
        $user_data['openid'] = isset($data['openId']) ? $data['openId'] : '';

        return $user_data;

        $add_res = logic('User')->xcxadd($add_data);
        if ($add_res['code'] != ReturnData::SUCCESS) {
            exit(json_encode(ReturnData::create($add_res['code'], null, $add_res['msg'])));
        }

        //生成token
        $data['token'] = logic('UserToken')->getToken($add_res['data']['id']);

        $data['id'] = $add_res['data']['id'];
        exit(json_encode(ReturnData::create(ReturnData::SUCCESS, $data)));
    }

    /**
     * 小程序获取手机号码，以code换取 用户唯一标识openid 和 会话密钥session_key
     * @param string $param ['code'] 用户登录凭证（有效期五分钟）。开发者需要在开发者服务器后台调用 auth.code2Session，使用 code 换取 openid 和 session_key 等信息
     * @param string $param ['encryptedData'] 包括敏感数据在内的完整用户信息的加密数据
     * @param string $param ['iv'] 加密算法的初始向量
     * @return array
     */
    public function getWechatUserMobile($param)
    {
        include_once APP_PATH . 'common/lib/wechat/aes/wxBizDataCrypt.php';
        /**
         * 3.小程序调用server获取token接口, 传入code, rawData, signature, encryptData.
         */
        $code = $data['code'] = $param['code'];
        $encryptedData = $data['encryptedData'] = $param['encryptedData'];
        $iv = $data['iv'] = $param['iv'];

        /**
         * 4.server调用微信提供的jsoncode2session接口获取openid, session_key, 调用失败应给予客户端反馈
         * , 微信侧返回错误则可判断为恶意请求, 可以不返回. 微信文档链接
         * 这是一个 HTTP 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。其中 session_key 是对用户数据进行加密签名的密钥。
         * 为了自身应用安全，session_key 不应该在网络上传输。
         * 接口地址："https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code"
         */
        if ($code == null || $code == '') {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }
        $wechat = new WechatAuth(sysconfig('CMS_WX_MINIPROGRAM_APPID'), sysconfig('CMS_WX_MINIPROGRAM_APPSECRET'));
        $res = $wechat->miniprogram_wxlogin($code);
        if (!isset($res['session_key'])) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, 'requestTokenFailed');
        }

        $session_key = $res['session_key'];

        /**
         *
         * 6.使用第4步返回的session_key解密encryptData, 将解得的信息与rawData中信息进行比较, 需要完全匹配,
         * 解得的信息中也包括openid, 也需要与第4步返回的openid匹配. 解密失败或不匹配应该返回客户相应错误.
         * （使用官方提供的方法即可）
         */
        $pc = new \WXBizDataCrypt(sysconfig('CMS_WX_MINIPROGRAM_APPID'), $session_key);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        if ($errCode != 0) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, 'encryptDataNotMatch');
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
        if (!$data) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '没有数据');
        }
        return ReturnData::create(ReturnData::SUCCESS, $data);
    }

    //用户修改手机号
    public function changeMobile($data, $where = array())
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::SUCCESS);
        }

        if (!(isset($data['mobile']) && !empty($data['mobile']) && Validator::isMobile($data['mobile']))) {
            return ReturnData::create(ReturnData::FAIL, null, '手机号格式不正确');
        }

        //更新时间
        if (!(isset($data['update_time']) && !empty($data['update_time']))) {
            $data['update_time'] = time();
        }

        //验证数据
        $validate = new Validate([
            ['code', 'require|number|max:6', '验证码不能为空|验证码必须是数字|验证码格式不正确'],
        ]);
        if (!$validate->check($data)) {
            return ReturnData::create(ReturnData::FAIL, null, $validate->getError());
        }

        //验证码验证
        $verify_code_check = logic('VerifyCode')->check(array('mobile' => $data['mobile'], 'code' => $data['code'], 'type' => VerifyCode::TYPE_CHANGE_MOBILE));
        if ($verify_code_check['code'] != ReturnData::SUCCESS) {
            return ReturnData::create(ReturnData::FAIL, null, $verify_code_check['msg']);
        }

        $record = $this->getModel()->getOne($where);
        if (!$record) {
            return ReturnData::create(ReturnData::RECORD_NOT_EXIST);
        }

        //判断手机号
        if (isset($data['mobile']) && $data['mobile'] != '') {
            $where_user_name['mobile'] = $data['mobile'];
            $where_user_name['id'] = ['<>', $record['id']]; //排除自身
            if ($this->getModel()->getOne($where_user_name)) {
                return ReturnData::create(ReturnData::FAIL, null, '该手机号已存在');
            }
        }

        $res = $this->getModel()->edit(array('mobile' => $data['mobile']), $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    /**
     * 增加累计消费金额
     * @param int $user_id 用户id
     * @param float $consumption_money 消费金额
     * @return array
     */
    public function changeConsumptionMoney($user_id, $consumption_money)
    {
        return model('User')->setIncrement(array('id' => $user_id), 'consumption_money', $consumption_money);
    }
}