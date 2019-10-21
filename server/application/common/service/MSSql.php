<?php
// +----------------------------------------------------------------------
// | MSSql的操作类
// +----------------------------------------------------------------------
namespace app\common\service;

class MSSql
{
    public $link;
    public $querynum = 0;

    //连接MSSql数据库，参数：dbsn->数据库服务器地址，dbun->登陆用户名，dbpw->登陆密码，dbname->数据库名字
    public function Connect($dbsn, $dbun, $dbpw, $dbname)
    {
        if ($this->link = @mssql_connect($dbsn, $dbun, $dbpw, true)) {
            $query = $this->Query('SET TEXTSIZE 2147483647');
            if (@mssql_select_db($dbname, $this->link)) {
            } else {
                $this->halt('Can not Select DataBase');
            }
        } else {
            $this->halt('Can not connect to MSSQL server');
        }
    }

    //执行sql语句，返回对应的结果标识
    public function Query($sql)
    {
        if ($query = @mssql_query($sql, $this->link)) {
            $this->querynum++;
            return $query;
        } else {
            $this->querynum++;
            $this->halt('MSSQL Query Error', $sql);
        }
    }

    //执行Insert Into语句，并返回最后的insert操作所产生的自动增长的id
    public function Insert($table, $iarr)
    {
        $value = $this->InsertSql($iarr);
        $query = $this->Query('INSERT INTO ' . $table . ' ' . $value . '; SELECT SCOPE_IDENTITY() AS [insertid];');
        $record = $this->GetRow($query);
        $this->Clear($query);
        return $record['insertid'];
    }

    //执行Update语句，并返回最后的update操作所影响的行数
    public function Update($table, $uarr, $condition = '')
    {
        $value = $this->UpdateSql($uarr);
        if ($condition) {
            $condition = ' WHERE ' . $condition;
        }
        $query = $this->Query('UPDATE ' . $table . ' SET ' . $value . $condition . '; SELECT @@ROWCOUNT AS [rowcount];');
        $record = $this->GetRow($query);
        $this->Clear($query);
        return $record['rowcount'];
    }

    //执行Delete语句，并返回最后的Delete操作所影响的行数
    public function Delete($table, $condition = '')
    {
        if ($condition) {
            $condition = ' WHERE ' . $condition;
        }
        $query = $this->Query('DELETE ' . $table . $condition . '; SELECT @@ROWCOUNT AS [rowcount];');
        $record = $this->GetRow($query);
        $this->Clear($query);
        return $record['rowcount'];
    }

    //将字符转为可以安全保存的mssql值，比如a'a转为a''a
    public function EnCode($str)
    {
        return str_replace("'", "''", str_replace('', '', $str));
    }

    //将可以安全保存的mssql值转为正常的值，比如a''a转为a'a
    public function DeCode($str)
    {
        return str_replace("''", "'", $str);
    }

    //将对应的列和值生成对应的insert语句，如：array('id' => 1, 'name' => 'name')返回([id], [name]) VALUES (1, 'name')
    public function InsertSql($iarr)
    {
        if (is_array($iarr)) {
            $fstr = '';
            $vstr = '';
            foreach ($iarr as $key => $val) {
                $fstr .= '[' . $key . '], ';
                $vstr .= '\'' . $val . '\', ';
            }
            if ($fstr) {
                $fstr = '(' . substr($fstr, 0, -2) . ')';
                $vstr = '(' . substr($vstr, 0, -2) . ')';
                return $fstr . ' VALUES ' . $vstr;
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    //将对应的列和值生成对应的insert语句，如：array('id' => 1, 'name' => 'name')返回[id] = 1, [name] = 'name'
    public function UpdateSql($uarr)
    {
        if (is_array($uarr)) {
            $ustr = '';
            foreach ($uarr as $key => $val) {
                $ustr .= '[' . $key . '] = \'' . $val . '\', ';
            }
            if ($ustr) {
                return substr($ustr, 0, -2);
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    //返回对应的查询标识的结果的一行
    public function GetRow($query, $result_type = MSSQL_ASSOC)
    {
        return mssql_fetch_array($query, $result_type);
    }

    //清空查询结果所占用的内存资源
    public function Clear($query)
    {
        return mssql_free_result($query);
    }

    //关闭数据库
    public function Close()
    {
        return mssql_close($this->link);
    }

    public function halt($message = '', $sql = '')
    {
        $message .= '<br />MSSql Error:' . mssql_get_last_message();
        if ($sql) {
            $sql = '<br />sql:' . $sql;
        }

        exit("DataBase Error.<br />Message $message $sql");
    }
}