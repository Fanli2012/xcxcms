<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Helper;
use app\common\lib\ReturnData;

class Image extends Common
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public $path;
    public $public_path;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->path = '/uploads/'.date('Y/m',time());
        $this->public_path = $_SERVER['DOCUMENT_ROOT'];
    }
    
    //单文件/图片上传，成功返回路径，不含域名
    public function imageUpload(Request $request)
	{
        $file = $_FILES['file'];//得到传输的数据
        
        $type = strtolower(substr(strrchr($file["name"], '.'), 1)); //文件后缀
        
        $image_path = $this->path.'/'.date('Ymdhis',time()).rand(1000,9999).'.'.$type;
        $uploads_path = $this->path; //存储路径
        
        $allow_type = array('jpg','jpeg','gif','png','doc','docx','txt','pdf'); //定义允许上传的类型
        
        //判断文件类型是否被允许上传
        if(!in_array($type, $allow_type))
        {
            //如果不被允许，则直接停止程序运行
            exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL,null,'文件格式不正确')));
        }
        
        //判断是否是通过HTTP POST上传的
        if(!is_uploaded_file($file['tmp_name']))
        {
            //如果不是通过HTTP POST上传的
            exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL)));
        }
        
        //文件小于1M
        if ($file["size"] < 2048000)
        {
            if ($file["error"] > 0)
            {
                exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL,null,$file["error"])));
            }
            else
            {
                if(!file_exists($this->public_path.$uploads_path))
                {
                    Helper::createDir($this->public_path.$uploads_path); //创建文件夹;
                }
                
                move_uploaded_file($file["tmp_name"], $this->public_path.$image_path);
            }
        }
        else
        {
            exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL,null,'文件不得超过2M')));
        }
        
        exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$image_path)));
    }
    
    /**
     * 多文件上传，成功返回路径，不含域名
     * 多文件上传格式：
     * <input type="file" name="file[]">
     * <input type="file" name="file[]">
     * <input type="file" name="file[]">
     */
    public function multipleImageUpload(Request $request)
	{
        $res = [];
        $file = $_FILES['file'];//得到传输的数据
        
        if($file)
        {
            foreach($file['name'] as $key=>$value)
            {
                $type = strtolower(substr(strrchr($file["name"][$key], '.'), 1)); //文件后缀
                
                $image_path = $this->path.'/'.date('Ymdhis',time()).rand(1000,9999).'.'.$type;
                $uploads_path = $this->path; //存储路径
                
                $allow_type = array('jpg','jpeg','gif','png','doc','docx','txt','pdf'); //定义允许上传的类型
                
                //判断文件类型是否被允许上传
                if(!in_array($type, $allow_type))
                {
                    //如果不被允许，则直接停止程序运行
                    exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL,null,'文件格式不正确')));
                }
                
                //判断是否是通过HTTP POST上传的
                if(!is_uploaded_file($file['tmp_name'][$key]))
                {
                    //如果不是通过HTTP POST上传的
                    exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL)));
                }
                
                //文件小于2M
                if ($file["size"][$key] < 2048000)
                {
                    if ($file["error"][$key] > 0)
                    {
                        exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL,null,$file["error"][$key])));
                    }
                    else
                    {
                        if(!file_exists($this->public_path.$uploads_path))
                        {
                            Helper::createDir($this->public_path.$uploads_path); //创建文件夹;
                        }
                        
                        move_uploaded_file($file["tmp_name"][$key], $this->public_path.$image_path);
                    }
                }
                else
                {
                    exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL,null,'文件不得超过2M')));
                }
                
                $res[] = $image_path;
            }
        }
        
        exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    /**
     * 多文件上传，成功返回路径，不含域名
     * 多文件上传格式：
     * <input type="file" name="file1">
     * <input type="file" name="file2">
     * <input type="file" name="file3">
     */
    public function multipleFileUpload(Request $request)
	{
        $res = [];
        $files = $_FILES;//得到传输的数据
        
        if($files)
        {
            foreach($files as $key=>$file)
            {
                $type = strtolower(substr(strrchr($file["name"], '.'), 1)); //文件后缀
                
                $image_path = $this->path.'/'.date('Ymdhis',time()).rand(1000,9999).'.'.$type;
                $uploads_path = $this->path; //存储路径
                
                $allow_type = array('jpg','jpeg','gif','png','doc','docx','txt','pdf'); //定义允许上传的类型
                
                //判断文件类型是否被允许上传
                if(!in_array($type, $allow_type))
                {
                    //如果不被允许，则直接停止程序运行
                    exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL,null,'文件格式不正确')));
                }
                
                //判断是否是通过HTTP POST上传的
                if(!is_uploaded_file($file['tmp_name']))
                {
                    //如果不是通过HTTP POST上传的
                    exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL)));
                }
                
                //文件小于1M
                if ($file["size"] < 2048000)
                {
                    if ($file["error"] > 0)
                    {
                        exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL,null,$file["error"])));
                    }
                    else
                    {
                        if(!file_exists($this->public_path.$uploads_path))
                        {
                            Helper::createDir($this->public_path.$uploads_path); //创建文件夹;
                        }
                        
                        move_uploaded_file($file["tmp_name"], $this->public_path.$image_path);
                    }
                }
                else
                {
                    exit(json_encode(ReturnData::create(ReturnData::SYSTEM_FAIL,null,'文件不得超过2M')));
                }
                
                $res[] = $image_path;
            }
        }
        
        exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
}