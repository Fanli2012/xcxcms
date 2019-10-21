<?php

namespace app\index\controller;

use think\Db;
use think\Log;
use think\Request;
use think\Session;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class Download extends Base
{
    //pdf下载
    public function pdf()
    {
        $file = $_GET['file'];
        $arr = explode('/', $file);
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="' . array_pop($arr) . '"');
        readfile(file);
    }

}