<?php

namespace app\common\model;

use app\common\service\Smsbao;
use app\common\lib\Helper;
use app\common\lib\Smtp;
use app\common\lib\ReturnData;
use app\common\lib\Sms;
use think\Db;

class EmailVerifyCode extends Base
{
    protected $pk = 'id';

    public function getDb()
    {
        return db('email_verify_code');
    }

    const STATUS_UNUSE = 0;
    const STATUS_USE = 1;                                                       //验证码已被使用

    const TYPE_GENERAL = 0;                                                     //通用
    const TYPE_REGISTER = 1;                                                    //用户注册业务验证码
    const TYPE_CHANGE_PASSWORD = 2;                                             //密码修改业务验证码
    const TYPE_MOBILEE_BIND = 3;                                                //手机绑定业务验证码
    const TYPE_VERIFYCODE_LOGIN = 4;                                            //验证码登录
    const TYPE_CHANGE_MOBILE = 5;                                               //修改手机号码

    /**
     * 验证码校验
     * @param int $code 验证码
     * @param string $email 邮箱
     * @param int $type 请求用途
     * @return array
     */
    public function isVerify($where)
    {
        $where2 = $where;
        $where['status'] = self::STATUS_UNUSE;
        $where['expire_time'] = array('>', time());
        $res = $this->getOne($where);
        if ($res) {
            $this->setVerifyCodeUse($where2);
        }

        return $res;
    }

    /**
     * 验证码设置为已使用
     * @param int $code 验证码
     * @param string $email 邮箱
     * @param int $type 请求用途
     * @return array
     */
    public function setVerifyCodeUse($where)
    {
        return $this->edit(array('status' => self::STATUS_USE), $where);
    }

    //生成验证码
    public function getVerifyCodeBySmtp($email, $type, $text = '')
    {
        $data['code'] = rand(1000, 9999);
        $data['type'] = $type;
        $data['email'] = $email;
        $data['status'] = self::STATUS_UNUSE;
        //30分钟有效
        $time = time();
        $data['expire_time'] = $time + 60 * 30;
        $data['add_time'] = $time;

        if ($text == '') {
            $text = '【' . sysconfig('CMS_WEBNAME') . '】您的验证码是' . $data['code'] . '，有效期30分钟。';
        }
        //短信发送验证码
        $smtpserver = 'smtp.sina.com';//SMTP服务器
        $smtpserverport = 25;//SMTP服务器端口
        $smtpusermail = '1feng2010@sina.com';//SMTP服务器的用户邮箱
        $smtpemailto = $email;//发送给谁
        $smtpuser = "1feng2010@sina.com";//SMTP服务器的用户帐号
        $smtppass = "seo123456";//SMTP服务器的用户密码
        $mailtitle = '【' . sysconfig('CMS_WEBNAME') . '】验证码';//邮件主题
        $mailcontent = $text;//邮件内容
        $mailtype = 'HTML';//邮件格式(HTML/TXT),TXT为文本邮件
        $smtp = new Smtp($smtpserver, $smtpserverport, true, $smtpuser, $smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
        $smtp->debug = false;//是否显示发送的调试信息
        $state = $smtp->sendmail($smtpemailto, $smtpusermail, $mailtitle, $mailcontent, $mailtype);
        if ($state == '') {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, '对不起，邮件发送失败！请检查邮箱填写是否有误。');
        }
        //添加验证码记录
        $this->add($data);

        return ReturnData::create(ReturnData::SUCCESS, array('code' => $data['code']));
    }

    /**
     * 列表
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $offset 偏移量
     * @param int $limit 取多少条
     * @return array
     */
    public function getList($where = array(), $order = '', $field = '*', $offset = 0, $limit = 15)
    {
        $res['count'] = self::where($where)->count();
        $res['list'] = array();

        if ($res['count'] > 0) {
            $res['list'] = self::where($where);

            if (is_array($field)) {
                $res['list'] = $res['list']->field($field[0], true);
            } else {
                $res['list'] = $res['list']->field($field);
            }

            if (is_array($order) && isset($order[0]) && $order[0] == 'orderRaw') {
                $res['list'] = $res['list']->orderRaw($order[1]);
            } else {
                $res['list'] = $res['list']->order($order);
            }

            $res['list'] = $res['list']->limit($offset . ',' . $limit)->select();
        }

        return $res;
    }

    /**
     * 分页，用于前端html输出
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $limit 每页几条
     * @param int|bool $simple 是否简洁模式或者总记录数
     * @param int $page 当前第几页
     * @return array
     */
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = 15, $simple = false)
    {
        $res = self::where($where);

        if (is_array($field)) {
            $res = $res->field($field[0], true);
        } else {
            $res = $res->field($field);
        }

        if (is_array($order) && isset($order[0]) && $order[0] == 'orderRaw') {
            $res = $res->orderRaw($order[1]);
        } else {
            $res = $res->order($order);
        }

        return $res->paginate($limit, $simple, array('query' => request()->param()));
    }

    /**
     * 查询全部
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $limit 取多少条
     * @return array
     */
    public function getAll($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = self::where($where);

        if (is_array($field)) {
            $res = $res->field($field[0], true);
        } else {
            $res = $res->field($field);
        }

        if (is_array($order) && isset($order[0]) && $order[0] == 'orderRaw') {
            $res = $res->orderRaw($order[1]);
        } else {
            $res = $res->order($order);
        }

        $res = $res->limit($limit)->select();

        return $res;
    }

    /**
     * 获取一条
     * @param array $where 条件
     * @param string $field 字段
     * @return array
     */
    public function getOne($where, $field = '*', $order = '')
    {
        $res = self::where($where);

        if (is_array($field)) {
            $res = $res->field($field[0], true);
        } else {
            $res = $res->field($field);
        }

        if (is_array($order) && isset($order[0]) && $order[0] == 'orderRaw') {
            $res = $res->orderRaw($order[1]);
        } else {
            $res = $res->order($order);
        }

        $res = $res->find();

        return $res;
    }

    /**
     * 添加
     * @param array $data 数据
     * @return int
     */
    public function add($data, $type = 0)
    {
        // 过滤数组中的非数据表字段数据
        // return $this->allowField(true)->isUpdate(false)->save($data);

        if ($type == 1) {
            // 添加单条数据
            //return $this->allowField(true)->data($data, true)->save();
            return self::strict(false)->insert($data);
        } elseif ($type == 2) {
            /**
             * 添加多条数据
             * $data = [
             *     ['foo' => 'bar', 'bar' => 'foo'],
             *     ['foo' => 'bar1', 'bar' => 'foo1'],
             *     ['foo' => 'bar2', 'bar' => 'foo2']
             * ];
             */

            //return $this->allowField(true)->saveAll($data);
            return self::strict(false)->insertAll($data);
        }

        // 新增单条数据并返回主键值
        return self::strict(false)->insertGetId($data);
    }

    /**
     * 修改
     * @param array $data 数据
     * @param array $where 条件
     * @return bool
     */
    public function edit($data, $where = array())
    {
        //return $this->allowField(true)->save($data, $where);
        return self::strict(false)->where($where)->update($data);
    }

    /**
     * 删除
     * @param array $where 条件
     * @return bool
     */
    public function del($where)
    {
        return self::where($where)->delete();
    }

    /**
     * 统计数量
     * @param array $where 条件
     * @param string $field 字段
     * @return int
     */
    public function getCount($where, $field = '*')
    {
        return self::where($where)->count($field);
    }

    /**
     * 获取最大值
     * @param array $where 条件
     * @param string $field 要统计的字段名（必须）
     * @return null
     */
    public function getMax($where, $field)
    {
        return self::where($where)->max($field);
    }

    /**
     * 获取最小值
     * @param array $where 条件
     * @param string $field 要统计的字段名（必须）
     * @return null
     */
    public function getMin($where, $field)
    {
        return self::where($where)->min($field);
    }

    /**
     * 获取平均值
     * @param array $where 条件
     * @param string $field 要统计的字段名（必须）
     * @return null
     */
    public function getAvg($where, $field)
    {
        return self::where($where)->avg($field);
    }

    /**
     * 统计总和
     * @param array $where 条件
     * @param string $field 要统计的字段名（必须）
     * @return null
     */
    public function getSum($where, $field)
    {
        return self::where($where)->sum($field);
    }

    /**
     * 查询某一字段的值
     * @param array $where 条件
     * @param string $field 字段
     * @return null
     */
    public function getValue($where, $field)
    {
        return self::where($where)->value($field);
    }

    /**
     * 查询某一列的值
     * @param array $where 条件
     * @param string $field 字段
     * @return array
     */
    public function getColumn($where, $field)
    {
        return self::where($where)->column($field);
    }

    /**
     * 某一列的值自增
     * @param array $where 条件
     * @param string $field 字段
     * @param int $step 默认+1
     * @return array
     */
    public function setIncrement($where, $field, $step = 1)
    {
        return self::where($where)->setInc($field, $step);
    }

    /**
     * 某一列的值自减
     * @param array $where 条件
     * @param string $field 字段
     * @param int $step 默认-1
     * @return array
     */
    public function setDecrement($where, $field, $step = 1)
    {
        return self::where($where)->setDec($field, $step);
    }

    /**
     * 打印sql
     */
    public function toSql()
    {
        return self::getLastSql();
    }

    //类型，0通用，注册，1:手机绑定业务验证码，2:密码修改业务验证码
    public function getTypeAttr($data)
    {
        $arr = array(0 => '通用', 1 => '手机绑定业务验证码', 2 => '密码修改业务验证码');
        return $arr[$data['type']];
    }

    //状态
    public function getStatusAttr($data)
    {
        $arr = array(0 => '未使用', 1 => '已使用');
        return $arr[$data['status']];
    }
}