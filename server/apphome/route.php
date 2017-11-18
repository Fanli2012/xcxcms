<?php
use	think\Route;

// 路由配置文件
// 把规则长的url放到前面，优先匹配，不然会出错，比如分页

//Route::rule('hello/:name','index/index/hello');

// 设置name变量规则（采用正则定义）
/* Route::pattern('name','\w+');
// 支持批量添加
Route::pattern([
    'name'  =>		'\w+',
    'id'    =>		'\d+',
]); */

//子域名路由配置，需在config开启【域名部署】'url_domain_deploy' => true
Route::domain('m',	function(){
    // 批量路由规则设置
    Route::rule([
        '/'              => array('wap/Index/index',array()),
        'tags'              => array('wap/Index/tags',array('ext'=>'html')),
        'search'            => 'wap/Index/search',
        'sitemap'              => array('wap/Index/sitemap',array('ext'=>'xml')),
        
        'p/<id>'            => array('wap/Index/detail',array()), //详情页
        'cat<cat>/<page>'   => array('wap/Index/category',array(),array('cat'=>'\d+','page'=>'\d+')), //分类页，分页
        'cat<cat>'          => ['wap/Index/category',[],['cat'=>'\d+']], //分类页
        
        'tag<tag>/<page>'   => array('wap/Index/tag',array('tag'=>'\d+','page'=>'\d+')), //标签页，分页
        'tag<tag>'          => array('wap/Index/tag',array('tag'=>'\d+')), //标签页
        
        'page/<id>'         => array('wap/Index/page',array('ext'=>'html'),array('id'=>'[a-zA-Z0-9]+')),
        
        //api路由
        'api/listarc'       => array('index/Server/listarc',array('method'=>'post')),
    ]);
});

return [
    /* '__pattern__' => [
        'name' => '\w+',
    ], */
    'tags'              => array('index/Index/tags',array('ext'=>'html')),
    'search'            => 'index/Index/search',
    'sitemap'              => array('index/Index/sitemap',array('ext'=>'xml')),
    
    'p/<id>'            => array('index/Index/detail',array()), //详情页
    'cat<cat>/<page>'   => array('index/Index/category',array(),array('cat'=>'\d+','page'=>'\d+')), //分类页，分页
    'cat<cat>'          => ['index/Index/category',array(),['cat'=>'\d+']], //分类页
    
    'tag<tag>/<page>'   => array('index/Index/tag',array('tag'=>'\d+','page'=>'\d+')), //标签页，分页
    'tag<tag>'          => array('index/Index/tag',array('tag'=>'\d+')), //标签页
    
    'page/<id>'         => array('index/Index/page',array('ext'=>'html'),array('id'=>'[a-zA-Z0-9]+')),
    
    //api路由
    'api/listarc'    => array('index/Server/listarc',array('method'=>'post')),
];