<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
namespace tests;

use app\api\controller\Cart;

class ApiCartTest extends ApiBase
{
    //购物车-列表
    public function testIndex()
    {
        $get_data = array(
            'access_token' => '123456'
        );
        $url = sysconfig('CMS_API_URL') . '/cart/index';
        $response = curl_request($url, $get_data, 'GET');

        $this->assertTrue(!!$response); //断言结果是否为true,如果不为true则报错
        $this->assertEquals('0', $response['code']); //断言结果是否等于0,如果不等于则报错
    }

}