<?php

namespace app\fladmin\controller;

class Index extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $this->assign('menus', model('menu')->getPermissionsMenu($this->admin_info['role_id']));

        $this->assign('module_name', request()->module());

        return $this->fetch();
    }

    public function welcome()
    {
        return $this->fetch();
    }

    public function upcache()
    {
        dir_delete(APP_PATH . '../runtime/');
        $this->success('缓存更新成功');
    }

    /**
     * 更新配置文件 / 更新系统缓存
     */
    public function updateconfig()
    {
        $str_tmp = "<?php\r\n"; //得到php的起始符。$str_tmp将累加
        $str_end = "?>"; //php结束符
        $str_tmp .= "//全站配置文件\r\n";

        $param = db("sysconfig")->select();
        foreach ($param as $row) {
            $str_tmp .= 'define("' . $row['varname'] . '","' . $row['value'] . '"); // ' . $row['info'] . "\r\n";
        }

        $str_tmp .= $str_end; //加入结束符
        //保存文件
        $sf = APP_PATH . "common.inc.php"; //文件名
        $fp = fopen($sf, "w"); //写方式打开文件
        fwrite($fp, $str_tmp); //存入内容
        fclose($fp); //关闭文件
        return $sf;
    }
}