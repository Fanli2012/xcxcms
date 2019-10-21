<?php
// +----------------------------------------------------------------------
// | 文件处理类
// +----------------------------------------------------------------------
namespace app\common\service;

class File
{
    /**
     * php远程下载文件并保存到指定路径
     * 示例
     * $url = "http://www.baidu.com/img/baidu_jgylogo3.gif"; 远程文件
     * $save_dir = "down/"; 保存到服务器的路径，不传表示当前路径
     * $filename = "test.gif"; 保存的文件名
     * $res = downloadRemoteFile($url, $save_dir, $filename, 1);
     * var_dump($res);
     */
    public static function downloadRemoteFile($url, $save_dir = '', $filename = '', $type = 0)
    {
        if (trim($url) == '') {
            return false;
        }
        if (trim($save_dir) == '') {
            $save_dir = './';
        }
        if (0 !== strrpos($save_dir, '/')) {
            $save_dir .= '/';
        }
        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
            return false;
        }
        //获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $content = ob_get_contents();
            ob_end_clean();
        }
        $size = strlen($content);
        //文件大小
        $fp2 = @fopen($save_dir . $filename, 'a');
        fwrite($fp2, $content);
        fclose($fp2);
        unset($content, $url);
        return array(
            'file_name' => $filename,
            'save_path' => $save_dir . $filename
        );
    }

    /**
     * 简单的下载文件
     * 示例
     * simpleDownFile("远程文件地址", "保存目录");
     */
    public static function simpleDownFile($url, $path)
    {
        $arr = parse_url($url);
        $fileName = basename($arr['path']);
        $file = file_get_contents($url);
        file_put_contents($path . $fileName, $file);
    }


}