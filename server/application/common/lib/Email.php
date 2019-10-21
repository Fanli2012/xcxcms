<?php
// +----------------------------------------------------------------------
// | 邮件发送类
// +----------------------------------------------------------------------
namespace app\common\lib;

class Email
{
    /**
     * 邮件服务器
     */
    private $email_server;
    /**
     * 端口
     */
    private $email_port = 25;
    /**
     * 账号
     */
    private $email_user;
    /**
     * 密码
     */
    private $email_password;
    /**
     * 发送邮箱
     */
    private $email_from;
    /**
     * 间隔符
     */
    private $email_delimiter = "\n";
    /**
     * 站点名称
     */
    private $email_send_name;

    public function get($key)
    {
        if (!empty($this->$key)) {
            return $this->$key;
        } else {
            return false;
        }
    }

    public function set($key, $value)
    {
        if (!isset($this->$key)) {
            $this->$key = $value;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送邮件
     *
     * @param string $email_to 发送对象邮箱地址
     * @param string $subject 邮件标题
     * @param string $message 邮件内容
     * @param string $from 页头来源内容
     * @return bool 布尔形式的返回结果
     */
    public function send($email_to, $subject, $message, $from = '')
    {
        if (empty($email_to)) return false;
        $message = base64_encode($this->html($subject, $message));
        $email_to = $this->to($email_to);
        $header = $this->header($from);

        /**
         * 发送
         */
        if (!$fp = @fsockopen($this->email_server, $this->email_port, $errno, $errstr, 30)) {
            $this->resultLog($this->email_server . ':' . $this->email_port . " CONNECT - Unable to connect to the SMTP server");
            return false;
        }
        stream_set_blocking($fp, true);

        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != '220') {
            $this->resultLog($this->email_server . ':' . $this->email_port . $lastmessage);
            return false;
        }

        fputs($fp, 'EHLO' . " abc\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250) {
            $this->resultLog($this->email_server . ':' . $this->email_port . " HELO/EHLO - $lastmessage");
            return false;
        } elseif (substr($lastmessage, 0, 3) == 220) {
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 250) {
                $this->resultLog($this->email_server . ':' . $this->email_port . " HELO/EHLO - $lastmessage");
                return false;
            }
        }
        while (1) {
            if (substr($lastmessage, 3, 1) != '-' || empty($lastmessage)) {
                break;
            }
            $lastmessage = fgets($fp, 512);
        }

        fputs($fp, "AUTH LOGIN\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 334) {
            $this->resultLog($this->email_server . ':' . $this->email_port . " AUTH LOGIN - $lastmessage");
            return false;
        }

        fputs($fp, base64_encode($this->email_user) . "\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 334) {
            $this->resultLog($this->email_server . ':' . $this->email_port . " USERNAME - $lastmessage");
            return false;
        }

        fputs($fp, base64_encode($this->email_password) . "\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 235) {
            $this->resultLog($this->email_server . ':' . $this->email_port . " PASSWORD - $lastmessage");
            return false;
        }

        fputs($fp, "MAIL FROM: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $this->email_from) . ">\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 250) {
            fputs($fp, "MAIL FROM: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $this->email_from) . ">\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 250) {
                $this->resultLog($this->email_server . ':' . $this->email_port . " MAIL FROM - $lastmessage");
                return false;
            }
        }

        fputs($fp, "RCPT TO: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_to) . ">\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 250) {
            fputs($fp, "RCPT TO: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_to) . ">\r\n");
            $lastmessage = fgets($fp, 512);
            $this->resultLog($this->email_server . ':' . $this->email_port . " RCPT TO - $lastmessage");
            return false;
        }

        fputs($fp, "DATA\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 354) {
            $this->resultLog($this->email_server . ':' . $this->email_port . " DATA - $lastmessage");
            return false;
        }

        fputs($fp, "Date: " . gmdate('r') . "\r\n");
        fputs($fp, "To: " . $email_to . "\r\n");
        fputs($fp, "Subject: " . $subject . "\r\n");
        fputs($fp, $header . "\r\n");
        fputs($fp, "\r\n\r\n");
        fputs($fp, "$message\r\n.\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 250) {
            $this->resultLog($this->email_server . ':' . $this->email_port . " END - $lastmessage");
        }
        fputs($fp, "QUIT\r\n");
        return true;
    }

    public function send_sys_email($email_to, $subject, $message)
    {
        $this->set('email_server', config('email.email_server'));
        $this->set('email_port', config('email.email_port'));
        $this->set('email_user', config('email.email_user'));
        $this->set('email_password', config('email.email_password'));
        $this->set('email_from', config('email.email_from'));
        $this->set('email_send_name', config('email.email_send_name'));
        $result = $this->send($email_to, $subject, $message);
        return $result;
    }

    /**
     * 内容:邮件主体
     *
     * @param string $subject 邮件标题
     * @param string $message 邮件内容
     * @return string 字符串形式的返回结果
     */
    private function html($subject, $message)
    {
        $tmp = '';
        //$message = preg_replace("/href\=\"(?!http\:\/\/)(.+?)\"/i", 'href="'.config('email_send_name').'\\1"', $message);
        $tmp .= "<html><head>";
        $tmp .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $tmp .= "<title>" . $subject . "</title>";
        $tmp .= "</head><body>" . $message . "</body></html>";
        $message = $tmp;
        unset($tmp);
        return $message;
    }

    /**
     * 发送对象邮件地址
     *
     * @param string $email_to 发送地址
     * @return string 字符串形式的返回结果
     */
    private function to($email_to)
    {
        $email_to = preg_match('/^(.+?) \<(.+?)\>$/', $email_to, $mats) ? ($this->email_user ? '=?utf-8?B?' . base64_encode($mats[1]) . "?= <$mats[2]>" : $mats[2]) : $email_to;
        return $email_to;
    }

    /**
     * 内容:邮件标题
     *
     * @param string $subject 邮件标题
     * @return string 字符串形式的返回结果
     */
    private function subject($subject)
    {
        $subject = '=?utf-8?B?' . base64_encode(preg_replace("/[\r|\n]/", '', '[' . $this->email_send_name . '] ' . $subject)) . '?=';
        return $subject;
    }

    /**
     * 内容:邮件主体内容
     *
     * @param string $message 邮件主体内容
     * @return string 字符串形式的返回结果
     */
    private function message($message)
    {
        $message = chunk_split(base64_encode(str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $message))))));
        return $message;
    }

    /**
     * 内容:邮件页头
     *
     * @param string $from 邮件页头来源
     * @return array $rs_row 返回数组形式的查询结果
     */
    private function header($from = '')
    {
        $message = '';
        if ($from == '') {
            $from = '=?utf-8?B?' . base64_encode($this->email_send_name) . "?= <" . $this->email_from . ">";
        } else {
            $from = preg_match('/^(.+?) \<(.+?)\>$/', $from, $mats) ? '=?utf-8?B?' . base64_encode($mats[1]) . "?= <$mats[2]>" : $from;
        }
        $header = "From: $from{$this->email_delimiter}";
        $header .= "X-Priority: 3{$this->email_delimiter}";
        $header .= "X-Mailer: abc {$this->email_delimiter}";
        $header .= "MIME-Version: 1.0{$this->email_delimiter}";
        $header .= "Content-type: text/html; ";
        $header .= "charset=utf-8" . "{$this->email_delimiter}";
        $header .= "Content-Transfer-Encoding: base64{$this->email_delimiter}";
        $header .= 'Message-ID: <' . gmdate('YmdHs') . '.' . substr(md5($message . microtime()), 0, 6) . rand(100000, 999999) . '@' . $_SERVER['HTTP_HOST'] . ">{$this->email_delimiter}";
        return $header;
    }

    /**
     * 错误信息记录
     *
     * @param string $msg 错误信息
     * @return bool 布尔形式的返回结果
     */
    private function resultLog($msg)
    {
        return true;
    }
}

?>