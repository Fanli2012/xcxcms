<?php
/**
 * 验证类
 */

namespace app\common\lib;

class Validator
{
    /**
     * @commit: 验证密码，密码可以包含6个或更多字母，数字，下划线_和连字符-
     * @function: isPWD
     * @param $value
     * @param int $minLen
     * @param int $maxLen
     * @return bool|int
     */
    public static function isPWD($value, $minLen = 6, $maxLen = 18)
    {
        $match = '/^[-_a-zA-Z0-9]{' . $minLen . ',' . $maxLen . '}$/i';
        $v = trim($value);
        if (empty($v))
            return false;
        return preg_match($match, $v);
    }

    /**
     * @commit:是否为空值
     * @function: isEmpty
     * @param $str
     * @return bool
     */
    public static function isEmpty($str)
    {
        $str = trim($str);
        return !empty($str) ? true : false;
    }

    /**
     * @commit:数字验证
     * @function: isNum
     * @param $str
     * @param string $flag int是否是整数，float是否是浮点型
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 17:59
     */
    public static function isNum($str, $flag = 'float')
    {
        if (!self::isEmpty($str)) return false;
        if (strtolower($flag) == 'int') {
            return ((string)(int)$str === (string)$str) ? true : false;
        } else {
            return ((string)(float)$str === (string)$str) ? true : false;
        }
    }

    /**
     * @commit: 验证用户名
     * @function: isNames
     * @param $value
     * @param int $minLen
     * @param int $maxLen
     * @param string $charset
     * @return bool|int
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:04
     */
    public static function isNames($value, $minLen = 2, $maxLen = 16, $charset = 'ALL')
    {
        if (empty($value))
            return false;
        switch ($charset) {
            case 'EN':
                $match = '/^[_\w\d]{' . $minLen . ',' . $maxLen . '}$/iu';
                break;
            case 'CN':
                $match = '/^[_\x{4e00}-\x{9fa5}\d]{' . $minLen . ',' . $maxLen . '}$/iu';
                break;
            default:
                $match = '/^[_\w\d\x{4e00}-\x{9fa5}]{' . $minLen . ',' . $maxLen . '}$/iu';
        }
        return preg_match($match, $value);
    }

    /**
     * @commit: 姓名昵称合法性检查，只能输入中文英文
     * @function: isName
     * @param $val
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:19
     */
    public static function isName($val)
    {
        if (preg_match("/^[\x80-\xffa-zA-Z0-9]{3,60}$/", $val)) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @commit: 名称匹配，如用户名，目录名等
     * @function: isUserName
     * @param $str 要匹配的字符串
     * @param bool $chinese 是否支持中文,默认支持，如果是匹配文件名，建议关闭此项（false）
     * @param string $charset 编码（默认utf-8,支持gb2312）
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:00
     */
    public static function isUserName($str, $chinese = true, $charset = 'utf-8')
    {
        if (!self::isEmpty($str)) return false;
        if ($chinese) {
            $match = (strtolower($charset) == 'gb2312') ? "/^[" . chr(0xa1) . "-" . chr(0xff) . "A-Za-z0-9_-]+$/" : "/^[x{4e00}-x{9fa5}A-Za-z0-9_]+$/u";
        } else {
            $match = '/^[A-za-z0-9_-]+$/';
        }
        return preg_match($match, $str) ? true : false;
    }

    /**
     * @commit:邮箱验证
     * @function: isEmail
     * @param $str
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime ct
     */
    public static function isEmail($str)
    {
        if (!self::isEmpty($str)) return false;
        return preg_match("/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i", $str) ? true : false;
    }

    /**
     * @commit: URL验证，纯网址格式，不支持IP验证
     * @function: isUrl
     * @param $str
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime ct
     */
    public static function isUrl($str)
    {
        if (!self::isEmpty($str)) return false;
        return preg_match('#(http|https|ftp|ftps)://([w-]+.)+[w-]+(/[w-./?%&=]*)?#i', $str) ? true : false;
    }

    /**
     * @commit: 检查一个（英文）域名是否合法
     * @function: isDomain
     * @param $Domain 域名
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:18
     */
    public static function isDomain($Domain)
    {
        if (!eregi("^[0-9a-z]+[0-9a-z\.-]+[0-9a-z]+$", $Domain)) {
            return FALSE;
        }
        if (!eregi("\.", $Domain)) {
            return FALSE;
        }

        if (eregi("\-\.", $Domain) or eregi("\-\-", $Domain) or eregi("\.\.", $Domain) or eregi("\.\-", $Domain)) {
            return FALSE;
        }

        $aDomain = explode(".", $Domain);
        if (!eregi("[a-zA-Z]", $aDomain[count($aDomain) - 1])) {
            return FALSE;
        }

        if (strlen($aDomain[0]) > 63 || strlen($aDomain[0]) < 1) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * @commit: 检查输入是否为英文
     * @function: isEnglish
     * @param $theelement
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:14
     */
    public static function isEnglish($theelement)
    {
        if (ereg("[\x80-\xff].", $theelement)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * @commit: 验证中文
     * @function: isChinese
     * @param $str 要匹配的字符串
     * @param string $charset 编码（默认utf-8,支持gb2312）
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:03
     */
    public static function isChinese($str, $charset = 'utf-8')
    {
        if (!self::isEmpty($str)) return false;
        $match = (strtolower($charset) == 'gb2312') ? "/^[" . chr(0xa1) . "-" . chr(0xff) . "]+$/"
            : "/^[x{4e00}-x{9fa5}]+$/u";
        return preg_match($match, $str) ? true : false;
    }

    /**
     * @commit: 检查是否输入为汉字
     * @function: isLetter
     * @param $sInBuf
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:13
     */
    public static function isLetter($sInBuf)
    {
        $iLen = strlen($sInBuf);
        for ($i = 0; $i < $iLen; $i++) {
            if (ord($sInBuf{$i}) >= 0x80) {
                if ((ord($sInBuf{$i}) >= 0x81 && ord($sInBuf{$i}) <= 0xFE) && ((ord($sInBuf{$i + 1}) >= 0x40 && ord($sInBuf{$i + 1}) < 0x7E) || (ord($sInBuf{$i + 1}) > 0x7E && ord($sInBuf{$i + 1}) <= 0xFE))) {
                    if (ord($sInBuf{$i}) > 0xA0 && ord($sInBuf{$i}) < 0xAA) {
                        //有中文标点
                        return FALSE;
                    }
                } else {
                    //有日文或其它文字
                    return FALSE;
                }
                $i++;
            } else {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * @commit: UTF-8验证
     * @function: isUtf8
     * @param $word
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:03
     */
    public static function isUtf8($word)
    {
        if (!self::isEmpty($word)) return false;
        return (preg_match("/^([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}/", $word)
            == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}$/", $word)
            == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){2,}/", $word)
            == true) ? true : false;
    }

    /**
     * @commit: 验证长度
     * @function: length
     * @param $str
     * @param int $type (方式，默认min <= $str <= max)
     * @param int $min 最小值
     * @param int $max 最大值
     * @param string $charset 字符
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:04
     */
    public static function length($str, $type = 3, $min = 0, $max = 0, $charset = 'utf-8')
    {
        if (!self::isEmpty($str)) return false;
        $len = mb_strlen($str, $charset);
        switch ($type) {
            case 1: //只匹配最小值
                return ($len >= $min) ? true : false;
                break;
            case 2: //只匹配最大值
                return ($max >= $len) ? true : false;
                break;
            default: //min <= $str <= max
                return (($min <= $len) && ($len <= $max)) ? true : false;
        }
    }

    /**
     * @commit: 检查字符串长度是否符合要求
     * @function: isNumLength
     * @param $val 字符串
     * @param $min 最小长度
     * @param $max 最大长度
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:15
     */
    public static function isNumLength($val, $min, $max)
    {
        $theelement = trim($val);
        if (ereg("^[0-9]{" . $min . "," . $max . "}$", $val))
            return TRUE;
        return FALSE;
    }

    /**
     * @commit: 检查字符串长度是否符合要求
     * @function: isEngLength
     * @param $val 字符串
     * @param $min 最小长度
     * @param $max 最大长度
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:15
     */
    public static function isEngLength($val, $min, $max)
    {
        $theelement = trim($val);
        if (ereg("^[a-zA-Z]{" . $min . "," . $max . "}$", $val))
            return TRUE;
        return FALSE;
    }

    /**
     * @commit: 验证邮箱
     * @function: checkZip
     * @param $str
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:05
     */
    public static function checkZip($str)
    {
        if (strlen($str) != 6) {
            return false;
        }
        if (substr($str, 0, 1) == 0) {
            return false;
        }
        return true;
    }

    /**
     * @commit: 匹配日期
     * @function: checkDate
     * @param $str
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:05
     */
    public static function checkDate($str)
    {
        $dateArr = explode("-", $str);
        if (is_numeric($dateArr[0]) && is_numeric($dateArr[1]) && is_numeric($dateArr[2])) {
            if (($dateArr[0] >= 1000 && $dateArr[0] <= 10000) && ($dateArr[1] >= 0 && $dateArr[1] <= 12) && ($dateArr[2] >= 0 && $dateArr[2] <= 31))
                return true;
            else
                return false;
        }
        return false;
    }

    /**
     * @commit: 匹配时间
     * @function: checkTime
     * @param $str
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:05
     */
    public static function checkTime($str)
    {
        $timeArr = explode(":", $str);
        if (is_numeric($timeArr[0]) && is_numeric($timeArr[1]) && is_numeric($timeArr[2])) {
            if (($timeArr[0] >= 0 && $timeArr[0] <= 23) && ($timeArr[1] >= 0 && $timeArr[1] <= 59) && ($timeArr[2] >= 0 && $timeArr[2] <= 59))
                return true;
            else
                return false;
        }
        return false;
    }

    /**
     * @commit: 验证是否日期的函数
     * @function: validateDate
     * @param $date
     * @param string $format
     * @return bool
     * @throws Exception
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:26
     */
    public static function validateDate($date, $format = 'YYYY-MM-DD')
    {
        switch ($format) {
            case 'YYYY/MM/DD':
            case 'YYYY-MM-DD':
                list($y, $m, $d) = preg_split('/[-./ ]/', $date);
                break;

            case 'YYYY/DD/MM':
            case 'YYYY-DD-MM':
                list($y, $d, $m) = preg_split('/[-./ ]/', $date);
                break;

            case 'DD-MM-YYYY':
            case 'DD/MM/YYYY':
                list($d, $m, $y) = preg_split('/[-./ ]/', $date);
                break;

            case 'MM-DD-YYYY':
            case 'MM/DD/YYYY':
                list($m, $d, $y) = preg_split('/[-./ ]/', $date);
                break;

            case 'YYYYMMDD':
                $y = substr($date, 0, 4);
                $m = substr($date, 4, 2);
                $d = substr($date, 6, 2);
                break;

            case 'YYYYDDMM':
                $y = substr($date, 0, 4);
                $d = substr($date, 4, 2);
                $m = substr($date, 6, 2);
                break;

            default:
                throw new Exception("Invalid Date Format");
        }
        return checkdate($m, $d, $y);
    }

    /**
     * 判断是否是日期格式
     * date函数会给月和日补零，所以最终用unix时间戳来校验
     */
    public static function isDate($date_string, $date_format = 'Y-m-d')
    {
        return strtotime(date($date_format, strtotime($date_string))) === strtotime($date_string);
    }

    /**
     * 判断值是否为时间戳
     */
    public static function isTimestamp($timestamp)
    {
        if (!is_numeric($timestamp)) {
            return false;
        }
        return strtotime(date('Y-m-d H:i:s', $timestamp)) === (int)$timestamp;
    }

    /**
     * @commit: 检查日期是否符合0000-00-00 00:00:00
     * @function: isTime
     * @param $sTime
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:11
     */
    public static function isTime($sTime)
    {
        if (ereg("^[0-9]{4}\-[][0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$", $sTime)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @commit: 检查输入IP是否符合要求
     * @function: isIp
     * @param $val
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:09
     */
    public static function isIp($val)
    {
        return (bool)ip2long($val);
    }

    /**
     * @commit: 获取客户端IP地址
     * @function: get_client_ip
     * @return array|false|string
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:30
     */
    public static function get_client_ip()
    {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return ($ip);
    }

    /**
     * @commit:
     * @function: isMoney
     * @param $val
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:10
     */
    public static function isMoney($val)
    {
        if (ereg("^[0-9]{1,}$", $val))
            return TRUE;
        if (ereg("^[0-9]{1,}\.[0-9]{1,2}$", $val))
            return TRUE;
        return FALSE;
    }

    /**
     * @commit: 检查输入的是否为邮编
     * @function: isPostcode
     * @param $val
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:23
     */
    public static function isPostcode($val)
    {
        if (ereg("^[0-9]{4,6}$", $val))
            return TRUE;
        return FALSE;
    }

    /**
     * @commit: 缩略图生成函数，最好使用GD2
     * @function: ImageResize
     * @param $srcFile 要生成缩略图的文件
     * @param $toW 缩略图宽度
     * @param $toH 缩略图高度
     * @param string $toFile 缩略图文件
     * @return bool
     * @author by stars<1014916675@qq.com>
     * @CreateTime 2017-09-22 18:28
     */
    public static function ImageResize($srcFile, $toW, $toH, $toFile = "")
    {
        if ($toFile == "") {
            $toFile = $srcFile;
        }
        $info = "";
        $data = GetImageSize($srcFile, $info);
        switch ($data[2]) {
            case 1:
                if (!function_exists("imagecreatefromgif")) {
                    //echo "你的GD库不能使用GIF格式的图片，请使用Jpeg或PNG格式！<a href='javascript:go(-1);'>返回</a>";
                    return false;
                }
                $im = ImageCreateFromGIF($srcFile);
                break;
            case 2:
                if (!function_exists("imagecreatefromjpeg")) {
                    //echo "你的GD库不能使用jpeg格式的图片，请使用其它格式的图片！<a href='javascript:go(-1);'>返回</a>";
                    return false;
                }
                $im = ImageCreateFromJpeg($srcFile);
                break;
            case 3:
                $im = ImageCreateFromPNG($srcFile);
                break;
        }
        $srcW = ImageSX($im);
        $srcH = ImageSY($im);
        $toWH = $toW / $toH;
        $srcWH = $srcW / $srcH;
        if ($toWH <= $srcWH) {
            $ftoW = $toW;
            $ftoH = $ftoW * ($srcH / $srcW);
        } else {
            $ftoH = $toH;
            $ftoW = $ftoH * ($srcW / $srcH);
        }
        if ($srcW > $toW || $srcH > $toH) {
            if (function_exists("imagecreatetruecolor")) {
                @$ni = ImageCreateTrueColor($ftoW, $ftoH);
                if ($ni) ImageCopyResampled($ni, $im, 0, 0, 0, 0, $ftoW, $ftoH, $srcW, $srcH);
                else {
                    $ni = ImageCreate($ftoW, $ftoH);
                    ImageCopyResized($ni, $im, 0, 0, 0, 0, $ftoW, $ftoH, $srcW, $srcH);
                }
            } else {
                $ni = ImageCreate($ftoW, $ftoH);
                ImageCopyResized($ni, $im, 0, 0, 0, 0, $ftoW, $ftoH, $srcW, $srcH);
            }
            if (function_exists('imagejpeg')) ImageJpeg($ni, $toFile);
            else ImagePNG($ni, $toFile);
            ImageDestroy($ni);
        } else {
            ImageDestroy($im);
            return false;
        }
        ImageDestroy($im);
        return true;
    }

    //验证是否是合法的手机号码
    public static function isMobile($mobile)
    {
        return preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $mobile);
    }

    //验证是否是合法的身份证号，简单验证
    public static function isIdCardNo($idcard)
    {
        $length = strlen($idcard);

        //15位老身份证
        if ($length == 15) {
            if (checkdate(substr($idcard, 8, 2), substr($idcard, 10, 2), '19' . substr($idcard, 6, 2))) {
                return true;
            }
        }

        //18位二代身份证号
        if ($length == 18) {
            if (!checkdate(substr($idcard, 10, 2), substr($idcard, 12, 2), substr($idcard, 6, 4))) {
                return false;
            }

            $idcard = str_split($idcard);
            if (strtolower($idcard[17]) == 'x') {
                $idcard[17] = '10';
            }

            //加权求和
            $sum = 0;
            //加权因子
            $wi = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1];
            for ($i = 0; $i < 17; $i++) {
                $sum += $wi[$i] * $idcard[$i];
            }

            //得到验证码所位置
            $position = $sum % 11;

            //身份证验证位值 10代表X
            $code = [1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2];
            if ($idcard[17] == $code[$position]) {
                return true;
            }
        }

        return false;
    }

    //验证是否是合法的银行卡，不包含信用卡
    public static function isBankCard($card)
    {
        if (!is_numeric($card)) {
            return false;
        }

        if (strlen($card) < 16 || strlen($card) > 19) {
            return false;
        }

        $cardHeader = [10, 18, 30, 35, 37, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 58, 60, 62, 65, 68, 69, 84, 87, 88, 94, 95, 98, 99];
        if (!in_array(substr($card, 0, 2), $cardHeader)) {
            return false;
        }

        $numShouldCheck = str_split(substr($card, 0, -1));
        krsort($numShouldCheck);

        $odd = $odd['gt9'] = $odd['gt9']['tens'] = $odd['gt9']['unit'] = $odd['lt9'] = $even = [];
        array_walk($numShouldCheck, function ($item, $key) use (&$odd, &$even, $card) {

            if ((strlen($card) == 16) && (substr($card, 0, 2) == '62')) {
                $key += 1;
            }

            if (($key & 1)) {
                $t = $item * 2;
                if ($t > 9) {
                    $odd['gt9']['unit'][] = intval($t % 10);
                    $odd['gt9']['tens'][] = intval($t / 10);
                } else {
                    $odd['lt9'][] = $t;
                }
            } else {
                $even[] = $item;
            }
        });

        $total = array_sum($even);
        array_walk_recursive($odd, function ($item, $key) use (&$total) {
            $total += $item;
        });

        $luhm = 10 - ($total % 10 == 0 ? 10 : $total % 10);

        $lastNumOfCard = substr($card, -1, 1);
        if ($luhm != $lastNumOfCard) {
            return false;
        }

        return true;
    }

    /**
     * 判断是否合法车牌号
     * name isCarLicense
     * author furong
     * param $license
     * return bool
     * @abstract
     * 2017年4月7日 14:06:17 增加对 特种车牌，武警车牌,军牌的校验
     * 2018年3月5日 13:32:18 增加对 6位新能源车牌的校验
     */
    public static function isCarLicense($license)
    {
        #匹配民用车牌和使馆车牌
        # 判断标准
        # 1，第一位为汉字省份缩写
        # 2，第二位为大写字母城市编码
        # 3，后面是5位仅含字母和数字的组合
        $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新使]{1}[A-Z]{1}[0-9a-zA-Z]{5}$/u";
        preg_match($regular, $license, $match);
        if (isset($match[0])) {
            return true;
        }

        #匹配特种车牌(挂,警,学,领,港,澳)
        #参考 https://wenku.baidu.com/view/4573909a964bcf84b9d57bc5.html
        $regular = '/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{4}[挂警学领港澳]{1}$/u';
        preg_match($regular, $license, $match);
        if (isset($match[0])) {
            return true;
        }

        #匹配武警车牌
        #参考 https://wenku.baidu.com/view/7fe0b333aaea998fcc220e48.html
        $regular = '/^WJ[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]?[0-9a-zA-Z]{5}$/ui';
        preg_match($regular, $license, $match);
        if (isset($match[0])) {
            return true;
        }

        #匹配军牌
        #参考 http://auto.sina.com.cn/service/2013-05-03/18111149551.shtml
        $regular = "/[A-Z]{2}[0-9]{5}$/";
        preg_match($regular, $license, $match);
        if (isset($match[0])) {
            return true;
        }

        #匹配新能源车辆6位车牌
        #参考 https://baike.baidu.com/item/%E6%96%B0%E8%83%BD%E6%BA%90%E6%B1%BD%E8%BD%A6%E4%B8%93%E7%94%A8%E5%8F%B7%E7%89%8C
        #小型新能源车
        $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[DF]{1}[0-9a-zA-Z]{5}$/u";
        preg_match($regular, $license, $match);
        if (isset($match[0])) {
            return true;
        }
        #大型新能源车
        $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{5}[DF]{1}$/u";
        preg_match($regular, $license, $match);
        if (isset($match[0])) {
            return true;
        }
    }
}