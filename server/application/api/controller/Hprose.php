<?php
namespace app\flapi\controller;
use think\Loader;

/**
 * ThinkPHP Hprose控制器类
 */
class Hprose
{
    protected $allowMethodList  =   '';
    protected $crossDomain      =   false;
    protected $P3P              =   false;
    protected $get              =   true;
    protected $debug            =   false;
	
   /**
     * 架构函数
     * @access public
     */
    public function __construct()
	{
        //控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
		
        //导入类库
        include(EXTEND_PATH.'hprose/HproseHttpServer.php'); //引入Hprose类
        //实例化HproseHttpServer
        $server         =   new \HproseHttpServer();
		
        if($this->allowMethodList)
		{
            $methods    =   $this->allowMethodList;
        }
		else
		{
            $methods    =   get_class_methods($this);
            $methods    =   array_diff($methods,array('__construct','_initialize','__call'));   
        }
		
        $server->addMethods($methods,$this);
		
		// Hprose设置
        if($this->debug)
		{
            $server->setDebugEnabled(true);
        }
        $server->setCrossDomainEnabled($this->crossDomain);
        $server->setP3PEnabled($this->P3P);
        $server->setGetEnabled($this->get);
		
        // 启动server
        $server->start();
    }
	
	/**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method,$args){}
}