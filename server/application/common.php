<?php
// 公共函数文件
/**
 * CURL
 * @param string $url 链接
 * @param array $params 参数
 * @param string $method 请求方式
 * @param array $headers 头部信息
 * @return array
 */
if (!function_exists('curl_request')) {
    function curl_request($url, $params = array(), $method = 'GET', $headers = array())
    {
        $curl = curl_init();

        switch (strtoupper($method)) {
            case 'GET' :
                if (!empty($params)) {
                    $url .= (strpos($url, '?') ? '&' : '?') . http_build_query($params);
                }
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                break;
            case 'POST' :
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
            case 'PUT' :
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
            case 'DELETE' :
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            return false;
        } else {
            // 解决windows 服务器 BOM 问题
            $response = trim($response, chr(239) . chr(187) . chr(191));
            $response = json_decode($response, true);
        }

        curl_close($curl);

        return $response;
    }
}

/**
 * 获取列表分页
 * @param $param ['pagenow'] 当前第几页
 * @param $param ['counts'] 总条数
 * @param $param ['pagesize'] 每页显示数量
 * @param $param ['catid'] 栏目id
 * @param $param ['offset'] 偏移量
 * @return array
 */
function get_listnav(array $param)
{
    $catid = $param["catid"];
    $pagenow = $param["pagenow"];
    $prepage = $nextpage = '';
    $prepagenum = $pagenow - 1;
    $nextpagenum = $pagenow + 1;

    $counts = $param["counts"];
    $totalpage = get_totalpage(array("counts" => $counts, "pagesize" => $param["pagesize"]));

    if ($totalpage <= 1 && $counts > 0) {
        return "<li><span class=\"pageinfo\">共1页/" . $counts . "条记录</span></li>";
    }
    if ($counts == 0) {
        return "<li><span class=\"pageinfo\">共0页/" . $counts . "条记录</span></li>";
    }
    $maininfo = "<li><span class=\"pageinfo\">共" . $totalpage . "页" . $counts . "条</span></li>";

    if (!empty($param["urltype"])) {
        $urltype = $param["urltype"];
    } else {
        $urltype = 'cat';
    }

    //获得上一页和下一页的链接
    if ($pagenow != 1) {
        if ($pagenow == 2) {
            $prepage .= "<li><a href='/" . $urltype . $catid . "'>上一页</a></li>";
        } else {
            $prepage .= "<li><a href='/" . $urltype . $catid . "/$prepagenum'>上一页</a></li>";
        }

        $indexpage = "<li><a href='/" . $urltype . $catid . "'>首页</a></li>";
    } else {
        $indexpage = "<li>首页</li>";
    }
    if ($pagenow != $totalpage && $totalpage > 1) {
        $nextpage .= "<li><a href='/" . $urltype . $catid . "/$nextpagenum'>下一页</a></li>";
        $endpage = "<li><a href='/" . $urltype . $catid . "/$totalpage'>末页</a></li>";
    } else {
        $endpage = "<li><a>末页</a></li>";
    }

    //获得数字链接
    $listdd = "";
    if (!empty($param["offset"])) {
        $offset = $param["offset"];
    } else {
        $offset = 2;
    }

    $minnum = $pagenow - $offset;
    $maxnum = $pagenow + $offset;

    if ($minnum < 1) {
        $minnum = 1;
    }
    if ($maxnum > $totalpage) {
        $maxnum = $totalpage;
    }

    for ($minnum; $minnum <= $maxnum; $minnum++) {
        if ($minnum == $pagenow) {
            $listdd .= "<li class=\"thisclass\">$minnum</li>";
        } else {
            if ($minnum == 1) {
                $listdd .= "<li><a href='/" . $urltype . $catid . "'>$minnum</a></li>";
            } else {
                $listdd .= "<li><a href='/" . $urltype . $catid . "/$minnum'>$minnum</a></li>";
            }
        }
    }

    $plist = '';
    $plist .= $indexpage; //首页链接
    $plist .= $prepage; //上一页链接
    $plist .= $listdd; //数字链接
    $plist .= $nextpage; //下一页链接
    $plist .= $endpage; //末页链接
    $plist .= $maininfo;

    return $plist;
}

/**
 * 获取列表上一页、下一页
 * @param $param ['pagenow'] 当前第几页
 * @param $param ['counts'] 总条数
 * @param $param ['pagesize'] 每页显示数量
 * @param $param ['catid'] 栏目id
 * @return array
 */
function get_prenext(array $param)
{
    $counts = $param['counts'];
    $pagenow = $param["pagenow"];
    $prepage = $nextpage = '';
    $prepagenum = $pagenow - 1;
    $nextpagenum = $pagenow + 1;
    $cat = $param['catid'];

    if (!empty($param["urltype"])) {
        $urltype = $param["urltype"];
    } else {
        $urltype = 'cat';
    }

    $totalpage = get_totalpage(array("counts" => $counts, "pagesize" => $param["pagesize"]));

    //获取上一页
    if ($pagenow == 1) {

    } elseif ($pagenow == 2) {
        $prepage = '<a class="prep" href="/' . $urltype . $cat . '.html">上一页</a> &nbsp; ';
    } else {
        $prepage = '<a class="prep" href="/' . $urltype . $cat . '/' . $prepagenum . '.html">上一页</a> &nbsp; ';
    }

    //获取下一页
    if ($pagenow < $totalpage && $totalpage > 1) {
        $nextpage = '<a class="nextp" href="/' . $urltype . $cat . '/' . $nextpagenum . '.html">下一页</a>';
    }

    $plist = '';
    $plist .= $indexpage; //首页链接
    $plist .= $prepage; //上一页链接
    $plist .= $nextpage; //下一页链接

    return $plist;
}

//根据总数与每页条数，获取总页数
function get_totalpage(array $param)
{
	$pagesize = CMS_PAGESIZE;
    if (isset($param['pagesize']) && $param['pagesize'] > 0) {
        $pagesize = $param["pagesize"];
    }
	
    $counts = $param["counts"];

    //取总数据量除以每页数的余数
    if ($counts % $pagesize) {
        $totalpage = intval($counts / $pagesize) + 1; //如果有余数，则页数等于总数据量除以每页数的结果取整再加一,如果没有余数，则页数等于总数据量除以每页数的结果
    } else {
        $totalpage = $counts / $pagesize;
    }

    return $totalpage;
}

/**
 * 获取当前URL
 * @param string|true $url URL地址 true 带域名获取
 * @return string
 */
function get_current_url($flag = false)
{
    $url = '';
    $is_cli = (PHP_SAPI == 'cli') ? true : false;
    if ($is_cli) {
        $url = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
    } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
        $url = $_SERVER['HTTP_X_REWRITE_URL'];
    } elseif (isset($_SERVER['REQUEST_URI'])) {
        $url = $_SERVER['REQUEST_URI'];
    } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
        $url = $_SERVER['ORIG_PATH_INFO'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
    }

    if ($url && $flag) {
        $url = http_host() . $url;
    }

    return $url;
}

//获取http(s)://+域名
function http_host($flag = false)
{
    $res = '';
    $protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    $res = "$protocol$_SERVER[HTTP_HOST]";
    if ($flag) {
        $res = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; //完整网址
    }

    return $res;
}

/**
 * 截取中文字符串
 * @param string $string 中文字符串
 * @param int $sublen 截取长度
 * @param int $start 开始长度 默认0
 * @param string $code 编码方式 默认UTF-8
 * @param string $omitted 末尾省略符 默认...
 * @return string
 */
function cut_str($string, $sublen = 250, $omitted = '', $start = 0, $code = 'UTF-8')
{
    $string = strip_tags($string);
    $string = str_replace("　", "", $string);
    $string = mb_strcut($string, $start, $sublen, $code);
    $string .= $omitted;
    return $string;
}

//PhpAnalysis获取中文分词
function get_participle($keyword)
{
    require_once EXTEND_PATH . 'phpAnalysis/phpAnalysis.php';

    //Vendor('phpAnalysis.phpAnalysis');
    //import("Vendor.phpAnalysis.phpAnalysis");
    //初始化类
    PhpAnalysis::$loadInit = false;
    $pa = new PhpAnalysis('utf-8', 'utf-8', false);
    //载入词典
    $pa->LoadDict();
    //执行分词
    $pa->SetSource($keyword);
    $pa->StartAnalysis(false);
    $keywords = $pa->GetFinallyResult(',');

    return ltrim($keywords, ",");
}

/**
 * 获取二维码
 * @param string $url url链接
 * @param int $size 点的大小：1到10,用于手机端4就可以了
 * @param string $level 纠错级别：L、M、Q、H
 * @return string
 */
function get_erweima($url, $size = 6, $level = 'H')
{
    require_once EXTEND_PATH . 'phpqrcode/qrlib.php';
    ob_start();
    \QRcode::png($url, false, $level, $size);
    $image_string = base64_encode(ob_get_contents());
    ob_end_clean();
    return 'data:image/jpg;base64,' . $image_string;
}

//判断是否是图片格式，是返回true
function imgmatch($url)
{
    $info = pathinfo($url);
    if (isset($info['extension'])) {
        if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png')) {
            return true;
        } else {
            return false;
        }
    }
}

//通过file_get_content获取远程数据
function http_request_post($url, $data, $type = 'POST')
{
    $content = http_build_query($data);
    $content_length = strlen($content);
    $options = array(
        'http' => array(
            'method' => $type,
            'header' =>
                "Content-type: application/x-www-form-urlencoded\r\n" .
                "Content-length: $content_length\r\n",
            'content' => $content
        )
    );

    $result = file_get_contents($url, false, stream_context_create($options));

    return $result;
}

function imageResize($url, $width, $height)
{
    header('Content-type: image/jpeg');

    list($width_orig, $height_orig) = getimagesize($url);
    $ratio_orig = $width_orig / $height_orig;

    if ($width / $height > $ratio_orig) {
        $width = $height * $ratio_orig;
    } else {
        $height = $width / $ratio_orig;
    }

    // This resamples the image
    $image_p = imagecreatetruecolor($width, $height);
    $image = imagecreatefromjpeg($url);
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
    // Output the image
    imagejpeg($image_p, null, 100);
}

//清空文件夹
function dir_delete($dir)
{
    //$dir = dir_path($dir);
    if (!is_dir($dir)) return FALSE;
    $handle = opendir($dir); //打开目录

    while (($file = readdir($handle)) !== false) {
        if ($file == '.' || $file == '..') continue;
        $d = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($d) ? dir_delete($d) : @unlink($d);
    }

    closedir($handle);
    return @rmdir($dir);
}

//读取动态配置
function sysconfig($varname = '')
{
    $sysconfig = cache('sysconfig');
    $res = '';

    if (empty($sysconfig)) {
        cache('sysconfig', NULL);

        $sysconfig = db('sysconfig')->field('varname,value')->select();

        cache('sysconfig', $sysconfig, 86400);
    }

    if ($varname != '') {
        foreach ($sysconfig as $row) {
            if ($varname == $row['varname']) {
                $res = $row['value'];
            }
        }
    } else {
        $res = $sysconfig;
    }

    return $res;
}

if (!function_exists('dd')) {
    function dd($data)
    {
        echo '<pre>';
        print_r($data);
        exit;
    }
}

/**
 * 获取数据属性
 * @param $dataModel 数据模型
 * @param $data 数据
 * @return array
 */
function getDataAttr($dataModel, $data = [])
{
    if (empty($dataModel) || empty($data)) {
        return false;
    }

    foreach ($data as $k => $v) {
        $_method_str = ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $k));

        $_method = 'get' . $_method_str . 'Attr';

        if (method_exists($dataModel, $_method)) {
            $data[$k . '_text'] = $dataModel->$_method($data);
        }
    }

    return $data;
}

//根据当前网站获取上一页下一页网址
function get_pagination_url($http_host, $query_string, $page = 0)
{
    $res = '';
    foreach (explode("&", $query_string) as $row) {
        if ($row) {
            $canshu = explode("=", $row);
            $res[$canshu[0]] = $canshu[1];
        }
    }

    if (isset($res['page'])) {
        unset($res['page']);
    }

    if ($page == 1 || $page == 0) {
    } else {
        $res['page'] = $page;
    }

    if ($res) {
        $res = $http_host . '?' . http_build_query($res);
    }

    return $res;
}

/**
 * 返回json
 * @param array $data
 */
function echo_json($data = array())
{
    // 返回JSON数据格式到客户端 包含状态信息
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode($data));
}

/**
 * 密码加密方法，可以考虑盐值包含时间（例如注册时间），
 * @param string $pass 原始密码
 * @return string 多重加密后的32位小写MD5码
 */
function password_encrypt($pass)
{
    if ('' == $pass) {
        return '';
    }
    $salt = config('password_salt');
    return md5(sha1($pass) . $salt);
}

//判断是否为数字
function checkIsNumber($data)
{
    if ($data == '' || $data == null) {
        return false;
    }

    if (preg_match("/^\d*$/", $data)) {
        return true;
    }

    return false;
}

/**
 * 调用服务接口
 * @param $name 服务类名称
 * @param array $config 配置
 * @return object
 */
function service($name = '', $config = [])
{
    static $instance = [];
    $guid = $name;
    //$guid = $name . 'Service';
    if (!isset($instance[$guid])) {
        $class = 'app\\common\\service\\' . ucfirst($name);
        if (class_exists($class)) {
            $service = new $class($config);
            $instance[$guid] = $service;
        } else {
            throw new Exception('class not exists:' . $class);
        }
    }

    return $instance[$guid];
}

/**
 * 调用逻辑接口
 * @param $name 逻辑类名称
 * @param array $config 配置
 * @return object
 */
function logic($name = '', $config = [])
{
    static $instance = [];
    $guid = $name . 'Logic';
    if (!isset($instance[$guid])) {
        $class = '\\app\\common\\logic\\' . ucfirst($name) . 'Logic';

        if (class_exists($class)) {
            $logic = new $class($config);
            $instance[$guid] = $logic;
        } else {
            throw new Exception('class not exists:' . $class);
        }
    }

    return $instance[$guid];
}

//时间戳转日期
function toDate($time, $format = 'Y-m-d H:i:s')
{
    if (empty ($time)) {
        return '';
    }
    $format = str_replace('#', ':', $format);
    return date($format, $time);
}


