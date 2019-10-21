<?php
namespace app\api\controller;
use app\common\lib\Helper;
use think\Log;

class Util
{
	/**
     * 数据集为JSON字符串
     * @access public
     * @param array $data 数据
     * @param integer $options json参数
     * @return string
     */
    public static function echo_json($data, $options = JSON_UNESCAPED_UNICODE)
    {
		Log::info('【返回】：'.json_encode($data));
		exit(json_encode($data, $options));
    }
}