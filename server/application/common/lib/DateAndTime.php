<?php
namespace app\common\lib;

class DateAndTime
{
    /**
     * 返回一定位数的时间戳，多少位由参数决定
     * 
     * @param digits 多少位的时间戳
     * @return 时间戳
     */
    public static function getTimestamp($digits = 10)
    {
        $digits = $digits > 10 ? $digits : 10;
        if ($digits > 10)
        {
            $digits = $digits - 10;
            return number_format(microtime(true),$digits,'','');
        }
        return time();
    }
    
}