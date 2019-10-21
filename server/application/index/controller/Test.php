<?php

namespace app\index\controller;

use think\Db;
use think\Log;
use think\Request;
use think\Session;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\ShopLogic;

class Test extends Base
{
    //图片上传
    public function formUploadimg()
    {
        return $this->fetch();
    }

}