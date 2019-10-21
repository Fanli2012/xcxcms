<?php

namespace app\fladmin\behavior;

use think\Db;

class Operation
{
    public function run()
    {
        $this->action_begin();
    }

    public function action_begin()
    {
        // 记录操作
        $controller = strtolower(request()->controller());
        $action = strtolower(request()->action());
        if ($controller != 'login' && $menu_name = model('Menu')->getValue(['module' => 'fladmin', 'controller' => $this->uncamelize(request()->controller()), 'action' => $action], 'name')) {
            $admin_info = session('admin_info');
            $data['content'] = $admin_info['name'] . $menu_name;
            $data['ip'] = request()->ip();
            $data['admin_id'] = $admin_info['id'];
            $data['admin_name'] = $admin_info['name'];
            $data['route'] = 'fladmin/' . $this->uncamelize(request()->controller()) . '/' . $action;
            $data['http_method'] = request()->method();
            $data['add_time'] = time();
            logic('AdminLog')->add($data);
        }
    }

    /**
     * 下划线转驼峰
     * 思路:
     * step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
     * step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符
     */
    public function camelize($uncamelized_words, $separator = '_')
    {
        $uncamelized_words = $separator . str_replace($separator, ' ', strtolower($uncamelized_words));
        return ltrim(str_replace(' ', '', ucwords($uncamelized_words)), $separator);
    }

    /**
     * 驼峰命名转下划线命名
     * 思路:
     * 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
     */
    public function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }
}