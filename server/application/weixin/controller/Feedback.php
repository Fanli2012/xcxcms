<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\FeedbackLogic;

class Feedback extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new FeedbackLogic();
    }
    
    //详情
    public function add()
	{
        return $this->fetch();
    }
}