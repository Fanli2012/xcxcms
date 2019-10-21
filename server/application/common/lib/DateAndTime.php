<?php

namespace app\common\lib;

class DateAndTime
{
    /**
     *  * 返回一定位数的时间戳，多少位由参数决定
     *  *
     *  * @param digits 多少位的时间戳
     *  * @return 时间戳
     *  */
    public static function getTimestamp($digits = 10)
    {
        $digits = $digits > 10 ? $digits : 10;
        if ($digits > 10) {
            $digits = $digits - 10;
            return number_format(microtime(true), $digits, '', '');
        }
        return time();
    }

    /**
     *  * 获取指定日期之间的所有月份
     * @param string $old_date 为开始日期 例2017-11-01 00:00:00
     * @param string $new_date 为结束日期 例2018-12-01 00:00:00
     *  * @return array
     *  */
    public function getTwoDateAllMonth($new_date = '', $old_date = '')
    {
        $arr = array();
        $step = 12;
        if (!$new_date) {
            $new_date = date('Y-m-d H:i:s');
        }
        $old_time = strtotime("-$step month", strtotime($new_date));
        if ($old_date) {
            $old_time = strtotime($old_date);
            $step = $this->getTwoDateMonthNum($old_date, $new_date);
        }

        for ($step; $step >= 0; --$step) {
            $t = strtotime("+$step month", $old_time);
            //$arr[] = date('Y-m',$t);
            $arr[] = explode('/', date('Y-m', $t) . '/' . date('Y-m-01 00:00:00', $t) . '/' . date('Y-m-', $t) . date('t', $t) . ' 23:59:59');
        }

        return $arr;
    }

    /**
     * 计算两个日期相差几个月
     * @param string $date1 为开始日期 例2017-11-01 00:00:00
     * @param string $date2 为结束日期 例2018-12-01 00:00:00
     * @return int
     */
    public function getTwoDateMonthNum($date1, $date2)
    {
        $date1_stamp = strtotime($date1);
        $date2_stamp = strtotime($date2);
        list($date_1['y'], $date_1['m']) = explode("-", date('Y-m', $date1_stamp));
        list($date_2['y'], $date_2['m']) = explode("-", date('Y-m', $date2_stamp));
        return abs($date_1['y'] - $date_2['y']) * 12 + $date_2['m'] - $date_1['m'];
    }

}