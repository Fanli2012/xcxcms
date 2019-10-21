<?php
// +----------------------------------------------------------------------
// | 常用的pdo操作类，支持mysql、sqlserver、oracle，有实例
// +----------------------------------------------------------------------
namespace app\common\service;

class Pdodb
{
    protected $pdo;
    protected $res;
    protected $config;

    //构造函数
    function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }

    //数据库连接
    public function connect()
    {
        try {
            $this->pdo = new \PDO($this->config['dsn'], $this->config['username'], $this->config['password']);//$dbh = new PDO('mysql:host=localhost;dbname=test', $user, $pass);
            $this->pdo->query("set names utf8");
        } catch (Exception $e) {
            echo '数据库连接失败,详情: ' . $e->getMessage() . ' 请在配置文件中数据库连接信息';
            exit ();
        }

        /*
        if($this->config['type']=='oracle'){
            $this->pdo->query("set names {$this->config['charset']};");
        }else{
            $this->pdo->query("set names {$this->config['charset']};");
        }
        */
        //把结果序列化成stdClass
        //$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        //自己写代码捕获Exception
        //$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);//属性名 属性值 数组以关联数组返回
    }

    //数据库关闭
    public function close()
    {
        $this->pdo = null;
    }

    //用于有记录结果返回的操作，特别是SELECT操作
    public function query($sql, $return = false)
    {
        $res = $this->pdo->query($sql);
        if ($res) {
            $this->res = $res; // 未返回 return $this->res;
        }

        if ($return) {
            return $res;
        }
    }

    //主要是针对没有结果集合返回的操作，比如INSERT、UPDATE、DELETE等操作
    public function exec($sql, $return = false)
    {
        $res = $this->pdo->exec($sql);
        if ($res) {
            $this->res = $res;
        }

        if ($return) {//返回操作是否成功 成功返回1 失败0
            return $res;
        }
    }

    //将$this->res以数组返回(全部返回)
    public function fetchAll()
    {
        return $this->res->fetchAll();
    }

    //将$this->res以数组返回(一条记录)
    public function fetch()
    {
        return $this->res->fetch();
    }

    //返回所有字段
    public function fetchColumn()
    {
        return $this->res->fetchColumn();
    }

    //返回最后插入的id
    public function lastInsertId()
    {
        return $this->res->lastInsertId();
    }

    //返回最后插入的id
    public function lastInsertId2()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * 参数说明
     * string/array $table 数据库表，两种传值模式
     * 普通模式：
     * 'tb_member, tb_money'
     * 数组模式：
     * array('tb_member', 'tb_money')
     * string/array $fields 需要查询的数据库字段，允许为空，默认为查找全部，两种传值模式
     * 普通模式：
     * 'username, password'
     * 数组模式：
     * array('username', 'password')
     * string/array $sqlwhere 查询条件，允许为空，两种传值模式
     * 普通模式(必须加上and,$sqlwhere为空 1=1 正常查询)：
     * 'and type = 1 and username like "%os%"'
     * 数组模式：
     * array('type = 1', 'username like "%os%"')
     * string $orderby 排序，默认为id倒序
     *int $debug 是否开启调试，开启则输出sql语句
     * 0 不开启
     * 1 开启
     * 2 开启并终止程序
     * int $mode 返回类型
     * 0 返回多条记录
     * 1 返回单条记录
     * 2 返回行数
     */
    public function select($table, $fields = "*", $sqlwhere = "", $orderby = "", $debug = 0, $mode = 0)
    {
        //参数处理
        if (is_array($table)) {
            $table = implode(', ', $table);
        }
        if (is_array($fields)) {
            $fields = implode(',', $fields);
            /*
            if($this->config['type']=='oracle'){
                //$fields = implode(',',$fields);//CUSTOMER_ID,FIRST_NAME,LAST_NAME,EMAIL
                //$fields = implode(",'UTF8','ZHS16GBK') ,convert(",$fields);
                //$fields="convert(".$fields.",'UTF8','ZHS16GBK')";
            }else{
                $fields = implode(',',$fields);
            }
            */
        }
        if (is_array($sqlwhere)) {
            $sqlwhere = ' and ' . implode(' and ', $sqlwhere);
        }

        //数据库操作
        if ($debug === 0) {
            if ($mode === 2) { //统计
                $this->query("select count(*) from $table where 1=1 $sqlwhere");
                $return = $this->fetchColumn();
            } else if ($mode === 1) { //返回一条
                $this->query("select $fields from $table where 1=1 $sqlwhere $orderby");
                $return = $this->fetch();
            } else {
                $this->query("select $fields from $table where 1=1 $sqlwhere $orderby");
                $return = $this->fetchAll();//如果 $this->res为空即sql语句错误 会提示Call to a member function fetchAll() on a non-object
            }
            return $return;
        } else {
            if ($mode === 2) {
                echo "select count(*) from $table where 1=1 $sqlwhere";
            } else if ($mode === 1) {
                echo "select $fields from $table where 1=1 $sqlwhere $orderby";
            } else {
                echo "select $fields from $table where 1=1 $sqlwhere $orderby";
            }
            if ($debug === 2) {
                exit;
            }
        }
    }

    /**
     * 参数说明
     * string/array $table 数据库表，两种传值模式
     * 普通模式：
     * 'tb_member, tb_money'
     * 数组模式：
     * array('tb_member', 'tb_money')
     * string/array $set 需要插入的字段及内容，两种传值模式
     * 普通模式：
     * 'username = "test", type = 1, dt = now()'
     * 数组模式：
     * array('username = "test"', 'type = 1', 'dt = now()')
     * int $debug 是否开启调试，开启则输出sql语句
     * 0 不开启
     * 1 开启
     * 2 开启并终止程序
     * int $mode 返回类型
     * 0 无返回信息
     * 1 返回执行条目数
     * 2 返回最后一次插入记录的id
     */
    public function oic_insert($table, $set, $debug = 0, $mode = 0)
    {
        //参数处理
        if (is_array($table)) {
            $table = implode(', ', $table);
        }
        if (is_array($set)) {
            $s = '';
            $i = 0;
            foreach ($set as $k => $v) {
                $i++;
                $s[$i] = $k;//,连接
                $val[$i] = $v;
            }
            $sarr = implode(",", $s);//去掉最后一个,
            //array_pop($sarr);
            $set = implode("','", $val);////15221579236','张三','','2001','8','4','女','是

            //$set = implode(', ', $set);
        }

        //数据库操作
        if ($debug === 0) {
            if ($mode === 2) {
                $this->query("insert into $table ($sarr) values('" . $set . "')");
                //$return = $this->lastInsertId();
            } else if ($mode === 1) {
                $this->exec("insert into $table ($sarr) values('" . $set . "')");
                $return = $this->res;
            } else {
                $this->query("insert into $table ($sarr) values('" . $set . "')");
                $return = NULL;
            }
            return $return;
        } else {
            echo "insert into $table ($sarr) values('" . $set . "')";
            if ($debug === 2) {
                exit;
            }
        }
    }

    public function insert($table, $set, $debug = 0, $mode = 0)
    {
        //参数处理
        if (is_array($table)) {
            $table = implode(', ', $table);
        }
        if (is_array($set)) {
            $s = '';
            foreach ($set as $k => $v) {
                $s .= $k . "='" . $v . "',";//,连接
            }
            $sarr = explode(',', $s);//去掉最后一个,
            array_pop($sarr);
            $set = implode(',', $sarr);

            //$set = implode(', ', $set);
        }

        //数据库操作
        if ($debug === 0) {
            if ($mode === 2) {
                $this->query("insert into $table set $set");
                $return = $this->pdo->lastInsertId();
            } else if ($mode === 1) {
                $this->exec("insert into $table set $set");
                $return = $this->res;
            } else {
                $this->query("insert into $table set $set");
                $return = NULL;
            }

            return $return;
        } else {
            echo "insert into $table set $set";
            if ($debug === 2) {
                exit;
            }
        }
    }

    /**
     * 参数说明
     * string $table 数据库表，两种传值模式
     * 普通模式：
     * 'tb_member, tb_money'
     * 数组模式：
     * array('tb_member', 'tb_money')
     * string/array $set 需要更新的字段及内容，两种传值模式
     * 普通模式：
     * 'username = "test", type = 1, dt = now()'
     * 数组模式：
     * array('username = "test"', 'type = 1', 'dt = now()')
     * string/array $sqlwhere 修改条件，允许为空，两种传值模式
     * 普通模式：
     * 'and type = 1 and username like "%os%"'
     * 数组模式：
     * array('type = 1', 'username like "%os%"')
     * int $debug 是否开启调试，开启则输出sql语句
     * 0 不开启
     * 1 开启
     * 2 开启并终止程序
     * int $mode 返回类型
     * 0 无返回信息
     * 1 返回执行条目数
     */
    public function update($table, $set, $sqlwhere = "", $debug = 0, $mode = 0)
    {
        //参数处理
        if (is_array($table)) {
            $table = implode(', ', $table);
        }
        if (is_array($set)) {
            $s = '';

            foreach ($set as $k => $v) {
                $s .= $k . "='" . $v . "',";
            }
            $sarr = explode(',', $s);//去掉最后一个,
            array_pop($sarr);
            $set = implode(',', $sarr);
            //$set = implode(', ', $set);
        }
        if (is_array($sqlwhere)) {
            $sqlwhere = ' and ' . implode(' and ', $sqlwhere);
        }
        //数据库操作
        if ($debug === 0) {
            if ($mode === 1) {
                $this->exec("update $table set $set where 1=1 $sqlwhere");
                $return = $this->res;
            } else {
                $this->query("update $table set $set where 1=1 $sqlwhere");
                $return = true;
            }
            return $return;
        } else {
            echo "update $table set $set where 1=1 $sqlwhere";
            if ($debug === 2) {
                exit;
            }
        }
    }

    /**
     * 参数说明
     * string $table 数据库表
     * string/array $sqlwhere 删除条件，允许为空，两种传值模式
     * 普通模式：
     * 'and type = 1 and username like "%os%"'
     * 数组模式：
     * array('type = 1', 'username like "%os%"')
     * int $debug 是否开启调试，开启则输出sql语句
     * 0 不开启
     * 1 开启
     * 2 开启并终止程序
     * int $mode 返回类型
     * 0 无返回信息
     * 1 返回执行条目数
     */
    public function delete($table, $sqlwhere = "", $debug = 0, $mode = 0)
    {
        //参数处理
        if (is_array($sqlwhere)) {
            $sqlwhere = ' and ' . implode(' and ', $sqlwhere); //是字符串需自己加上and
        }
        //数据库操作
        if ($debug === 0) {
            if ($mode === 1) {
                $this->exec("delete from $table where 1=1 $sqlwhere");
                $return = $this->res;
            } else {
                $this->query("delete from $table where 1=1 $sqlwhere");
                $return = NULL;
            }
            return $return;
        } else {
            echo "delete from $table where 1=1 $sqlwhere";
            if ($debug === 2) {
                exit;
            }
        }
    }

    /**
     * 预处理
     *
     * 通过数组值向预处理语句传递值
     * $sth = $dbh->prepare('SELECT name, colour, calories FROM fruit WHERE calories < ? AND colour = ?');
     * $sth->execute(array(150, 'red'));
     * $red = $sth->fetchAll();
     */
    public function prepare($sql)
    {
        $res = $this->pdo->prepare($sql);
        return $res;
    }
}

//示例
/*
sqlserver 配置 extension=php_pdo_mssql.dll和extension=php_pdo_sqlsrv.dll 安装对应的 ntwdblib.dll
http://msdn.microsoft.com/en-us/library/cc296170.aspx 下载php版本对应的sqlsrv扩展
sqlserver 配置 odbc连接需开启extension=php_pdo_odbc.dll
*/

/*
$mssql2008_config=array(
    'dsn'=>'odbc:Driver={SQL Server};Server=192.168.1.60;Database=his',//数据库服务器地址
	'username'=>'sa',
	'password'=>'xxxxx',
);
$mssql=new Pdodb($mssql2008_config);
$sql="select * from 
(
	select row_number()over(order by tempcolumn)temprownumber,*
		from (
			select top 10 tempcolumn=0,a.*
			from DA_GR_HBFS a 
			where 1=1
		) t
) tt
where temprownumber>0"; 
$mssql->query($sql);
while($res=$mssql->fetch()){
	$data[]=$res;
}
print_r($data);exit;
 
//mysql 操作
$msyql_config=array(
	'dsn'=>'mysql:host=localhost;dbname=talk',
	'username'=>'root',
	'password'=>'123456'
);
$mysql=new PDO_DB($msyql_config);
$sql = 'SELECT user_id, user_name, nickname FROM et_users ';
$mysql->query($sql);
$data=$mysql->fetchAll();
print_r($data);exit;
 
//oracle 操作
$oci_config=array(
	'dsn'=>'oci:dbname=orcl',
	'username'=>'BAOCRM',
	'password'=>'BAOCRM'
);
$oracle=new PDO_DB($oci_config);
//print_r($oracle);exit;//PDO_DB Object ( [pdo:protected] => PDO Object ( ) [res:protected] => [config:protected] => [Config] => Array ( [dsn] => oci:dbname=orcl [name] => PWACRM [password] => PWACRM ) )
$sql="select * from CUSTOMER_LEVEL t";
$oracle->query($sql);	
$data=$oracle->fetchAll();
print_r($data);exit;
*/

/*
Array
(
    [0] => Array
        (
            [LEVEL_ID] => 1
            [0] => 1
            [LEVEL_NAME] => 普通会员
            [1] => 普通会员
            [LEVEL_DETAIL] => 普通会员
            [2] => 普通会员
            [SORT_NUMBER] => 15
            [3] => 15
            [CREATE_TIME] => 12-7月 -12
            [4] => 12-7月 -12
            [CREATE_BY] => 1
            [5] => 1
            [UPDATE_TIME] => 12-7月 -12
            [6] => 12-7月 -12
            [UPDATE_BY] => 1
            [7] => 1
            [STATE] => 正常
            [8] => 正常
        )
 
)*/

/*
$res = [];
$pdodb = new Pdodb(config('site.config'));

$value = iconv('UTF-8', 'GB2312', $where['carno']);
$sql = "exec get_report_cfx ".$value.",'',''";
$stmt = $pdodb->query($sql, true);
$i = 1;

if($stmt)
{
    do {
        $rowset = $stmt->fetchAll();
        if ($rowset) { //想想这里为什么要先判断一下
            $tmp = [];
            foreach($rowset as $k=>$v)
            {
                foreach($v as $key=>$value)
                {
                    $tmp[$k][$key] = iconv('GB2312//IGNORE', 'UTF-8//IGNORE', $value);
                    //$tmp[$k][$key] = mb_convert_encoding( $value, "UTF-8", "GB2312" );
                }
            }
            
            $res[] = $tmp;
        }
        $i++;
    } while ($stmt->nextRowset());
}

return $res;
*/