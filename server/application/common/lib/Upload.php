<?php
// +----------------------------------------------------------------------
// | Upload 上传服务
// +----------------------------------------------------------------------
namespace app\common\lib;

class Upload
{
    //上传默认配制
    private $config = [
        //默认路径
        'uploadPath' => 'default',
        //上传类型
        'FilExt' => 'jpg,jpeg,png,gif,bmp',
        //图片名称
        'fileName' => '',
        //上传大小，M
        'MaxSize' => 2,
        //是否生成缩略图
        'isThumb' => 0,
        //缩略图宽度
        'thumbWidth' => 150,
        //缩略图高度
        'thumbHeight' => 150,
    ];

    //上传错误信息
    private $upload_error = '';

    //文件上传信息
    private $upload_data = ['file_name' => '', 'thumb_name' => ''];

    public function __construct($_config = [])
    {
        //加载系统配制
        $this->config = array_merge($this->config, config('site.upload'));
        if ($_config) {
            $this->config = array_merge($this->config, $_config);
        }
    }

    /**
     * 自定义配制
     *
     * @param 配制
     */
    public function config($_config)
    {
        $this->config = array_merge($this->config, $_config);
    }

    /**
     * 获取错误信息
     *
     * @param 配制
     */
    public function getUploadError()
    {
        return $this->upload_error;
    }

    /**
     * 获取上传信息
     */
    public function getUploadInfo()
    {
        return $this->upload_data;
    }

    /**
     * 上传
     *
     * @param $_filekey input name
     */
    public function upload($_filekey = '')
    {
        //获取文件
        $fileKey = $_filekey;
        if (empty($fileKey)) {
            $fileKey = key($_FILES);
        }

        $file = request()->file($fileKey);
        if ($file === null) {
            $this->upload_error = '上传文件不存在';
            return false;
        }
        if ($this->config['uploadPath'] == 'default') {
            $this->config['uploadPath'] = $this->config['uploadPath'] . DS;
        }
        $savePath = ROOT_PATH . PATH_UPLOADS . DS . $this->config['uploadPath'] . DS;
        //上传名称
        $save_name = true;
        if (!empty($this->config['fileName'])) {
            $save_name = $this->config['fileName'];
        }
        //上传
        $file_info = $file->rule('uniqid')->validate(['size' => $this->config['MaxSize'] * 1024, 'ext' => $this->config['FilExt']])->move($savePath, $save_name);
        if ($file_info) {
            $fileName = $file_info->getSaveName();
            $this->upload_data['file_name'] = $fileName;

            //缩略图
            if ($this->config['isThumb']) {
                $imageSrc = $savePath . $fileName;
                $image = \org\image\Image::open($imageSrc);
                //缩略图路径
                $thumbName = str_replace('.', '_thumb.', $imageSrc);
                $image->thumb($this->config['thumbWidth'], $this->config['thumbHeight'])->save($thumbName, null, 90);
                $thumbName = ($thumbName == null) ? $fileName : str_replace('.', '_thumb.', $fileName);
                $this->upload_data['thumb_name'] = $thumbName;
            }
            return true;
        } else {
            $this->upload_error = $file->getError();
            return false;
        }
    }
}