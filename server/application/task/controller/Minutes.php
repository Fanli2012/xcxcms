<?php
/**
 * 定时自动执行任务控制器
 */

namespace app\task\controller;

class Minutes extends Base
{
    /**
     * 初始化方法,最前且始终执行
     */
    public function _initialize()
    {
        // 只可以以cli方式执行
        if (!$this->request->isCli())
            $this->error('Autotask script only work at client!');

        parent::_initialize();
        // 清除错误
        error_reporting(0);
        // 设置永不超时
        set_time_limit(0);
    }

    public function index()
    {
        //玻璃卷转HTML
        $this->pdftohtml();
    }

    /**
     * 把用户的剥离卷中的pdf文件转化为html
     */
    public function pdftohtml()
    {
        $map['status'] = 0;
        $pdftohtmlModel = model('pdftohtml');
        $list = $pdftohtmlModel->getPdftohtmlList($map, 'id desc', '*', 30);
        $site_root_path = '/data/html/tkd/';
        $update_arr = [];//生成成功的文件
        foreach ($list as $key => $value) {
            //网站路径
            $file_path = PATH_UPLOADS . PATH_MEMBER . $value['member_id'] . DS . PATH_CLAIMS_DOC;
            //PDF路径
            $file_pdf = $site_root_path . $file_path . $value['pdf_file'];
            $file_html = $site_root_path . $file_path;
            //执行命令
            $ex = '/usr/local/bin/pdf2htmlEX --zoom 1.3 --dest-dir ' . $file_html . ' ' . $file_pdf;
            if (is_file($file_pdf)) {
                exec($ex, $output, $return);
                if (0 == $return) {
                    $update_arr[$key]['id'] = $value['id'];
                    $update_arr[$key]['status'] = 1;
                }
            }
        }
        if ($update_arr) {
            $pdftohtmlModel->saveAll($update_arr);
        }
    }

    public function pdftohtmlByPhpCli($pdf_file = '')
    {
        if (empty($pdf_file)) {
            return FALSE;
        }
        if (!is_file($pdf_file)) {
            return FALSE;
        }
        $pos = strripos($pdf_file, '/');
        $filename = substr($pdf_file, $pos + 1, -4);
        exec("/usr/local/bin/pdf2htmlEX /data/html/tkd/$pdf_file /" . dirname($pdf_file) . "/$filename.html");
    }

}
