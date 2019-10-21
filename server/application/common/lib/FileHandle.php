<?php

namespace app\common\lib;
/**
 * 文件处理类
 */
class FileHandle
{
    /**
     * 判断文件是否存在，支持本地及远程文件
     * @param String $file 文件路径
     * @return Boolean
     * --------------------示例--------------------
     * // 屏蔽域名不存在等访问问题的警告
     * error_reporting(E_ALL ^ (E_WARNING|E_NOTICE));
     * $file2 = 'http://www.csdn.net/css/logo.png';
     * check_file_exists($file2); // true
     */
    public static function check_file_exists($file)
    {
        // 远程文件
        if (strtolower(substr($file, 0, 4)) == 'http') {
            $header = get_headers($file, true);
            return isset($header[0]) && (strpos($header[0], '200') || strpos($header[0], '304'));
        }

        // 本地文件
        return file_exists($file);
    }
}