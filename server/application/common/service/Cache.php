<?php
// +----------------------------------------------------------------------
// | 缓存
// +----------------------------------------------------------------------
namespace app\common\service;

class Cache
{
    private $appkey;
    private $secret;

    public function __construct()
    {

    }

    /**
     * 读取缓存信息
     *
     * @param string $key 要取得缓存键
     * @param string $prefix 键值前缀
     * @param string $fields 所需要的字段
     * @return array/bool
     */
    public function rcache($key = null, $prefix = '', $fields = '*')
    {
        if ($key === null || !C('cache_open')) return array();
        $ins = Cache::getInstance('cacheredis');
        $cache_info = $ins->hget($key, $prefix, $fields);
        if ($cache_info === false) {
            //取单个字段且未被缓存
            $data = array();
        } elseif (is_array($cache_info)) {
            //如果有一个键值为false(即未缓存)，则整个函数返回空，让系统重新生成全部缓存
            $data = $cache_info;
            foreach ($cache_info as $k => $v) {
                if ($v === false) {
                    $data = array();
                    break;
                }
            }
        } else {
            //string 取单个字段且被缓存
            $data = array($fields => $cache_info);
        }
        // 验证缓存是否过期
        if (isset($data['cache_expiration_time']) && $data['cache_expiration_time'] < TIMESTAMP) {
            $data = array();
        }
        return $data;
    }

    /**
     * 写入缓存
     *
     * @param string $key 缓存键值
     * @param array $data 缓存数据
     * @param string $prefix 键值前缀
     * @param int $period 缓存周期  单位分，0为永久缓存
     * @return bool 返回值
     */
    public function wcache($key = null, $data = array(), $prefix, $period = 0)
    {
        if ($key === null || !C('cache_open') || !is_array($data)) return;
        $period = intval($period);
        if ($period != 0) {
            $data['cache_expiration_time'] = TIMESTAMP + $period * 60;
        }
        $ins = Cache::getInstance('cacheredis');
        $ins->hset($key, $prefix, $data);
        $cache_info = $ins->hget($key, $prefix);
        return true;
    }

    /**
     * 删除缓存
     *
     * @param string $key 缓存键值
     * @param string $prefix 键值前缀
     * @return boolean
     */
    public function dcache($key = null, $prefix = '')
    {
        if ($key === null || !C('cache_open')) return true;
        $ins = Cache::getInstance('cacheredis');
        return $ins->hdel($key, $prefix);
    }
}