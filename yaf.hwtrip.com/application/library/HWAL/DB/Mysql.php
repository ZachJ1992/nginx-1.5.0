<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWAL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: yeqiaohui<yeqiaohui@huawei.com>                             |
// |          qiaoguoqiang<qiaoguoqiang@huawei.com>                       |
// +----------------------------------------------------------------------+

/**
 * Master/Slave DB类
 * 
 * mysqldns参数从配置application.ini中获取
 * application.mysqldns[] = "192.168.205.252||3306||hwtrip2||sl||sl||UTF-8"
 *                           host||port||database||user||pass||charset
 * 可多次重复
 * 
 * 1) mysqldns仅有一组配置 : master/slave相同
 * 2) mysqldns有两组配置 : master使用第一个， slave使用第二个配置
 * 3) mysqldns有两组以上配置 : master使用第一个，后面的都是slave, slave选择可以使用随机方式实现
 * 
 * 注意：
 * 关于master/slave，如果master使用主备，那么在主备切换的时候， 需要更新application.ini中的mysqldns的第一组配置
 * 关于多个slave的选择，可以使用一定的算法来实现， 后续扩展修改相应的方法即可
 * 
 *
 *
 * @usage 
 *  $db = HWAL_DB_Mysql::getInstance();            // 获取单例
 *  $db->query($sql)                          // 查询
 *  $db->fetchAll()                           // 获取结果集
 *  $db->insert(...)                          // 插入数据行
 *  ....
 */

class HWAL_DB_Mysql
{
    /**
      * 单例模式
      *
      * @var resource
      */
    private static $instance = null;
    /**
     * 当前查询SQL信息
     * array('sql' => '', 'type' => master | slave)
     * @var array
     */
    protected static $_sql = array();
    /**
     * master mysqli 对象
     * @var mixed
     */
    protected static $master = null;
    /**
     * slave mysqli 对象
     * @var resource
     */
    protected static $slave = null;

    /**
     * mysql dns 数组
     * @var array
     */
    protected static $_dns = array();
    /**
     * 当前结果集
     * @var resource
     */
    protected static $_result = false;
    /**
     * 错误信息
     * @var string
     */
    protected static $_error = '';
    /**
     * 错误日志位置
     * @var string
     */
    protected static $_log = '/tmp/mysql-error.log';
    /**
      * 数据库执行记录
      *
      * @var array
      */
    public static $querys = array("times"=>0,"sqls"=>array());

    public function __construct()
    {
        self::$_dns = Yaf_Registry::get('mysqldns')->toArray();
    }

    /**
     * 获取单例
     */
    public static function getInstance(){
        if(!self::$instance){
            self::$instance = new HWAL_DB_Mysql();
        }
        return self::$instance;
    }

    /**
      * 执行指令
      * 这里需要检查查询的类型， 然后根据类型调用master|slave mysql实例
      * @param string $sql
      * @return resource
      */
    public function query($sql){
        self::$querys['times']++;
        $conn = $this->_checkSql($sql);
        if($conn)
        {
            $queryInfo = array("sql"=>$sql,"type"=>$conn['type']);
            self::$querys['sqls'][] = $queryInfo;
            self :: $_sql = $queryInfo;
            self :: $_result = $conn['conn'] -> query($sql);
            if ($conn['conn'] -> error) {
                self :: $_error = $conn['conn'] -> error;
                self :: log();
                return false;
            }
            return self :: $_result;
        } else {
            return false;
        }
    }

    /**
     * 取结果$result中第一行记录
     *
     * @param object $result    查询结果数据集
     * @param  boolean $assoc   true 返回数组; false 返回stdClass对象;默认 true
     * @return mixed            没有结果返回 false
     */
    public function fetchRow($result = null , $assoc = true) {
        if ($result == null) $result = self :: $_result;
        if (empty($result)) {
            return false;
        }
        if ($assoc) {
            $_result = $result -> fetch_assoc();
            if($_result) $_result = array_change_key_case($_result, CASE_UPPER);
            return $_result;
        } else {
            return $result -> fetch_object();
        }
    }

    /**
     * 取结果(self::$result)中第一行，第一列值
     *
     * @return mixed 没有结果返回 false
     */
    public function fetchOne() {
        if (!empty(self :: $_result)) {
            $row = self :: $_result -> fetch_array();
            return $row[0];
        } else {
            return false;
        }
    }

    /**
     * 查询指定SQL 第一行，第一列 值
     *
     * @param string $sql   SQL查询语句
     * @return mixed        失败返回 false
     */
    public function dataOne($sql) {
        if (self :: $_result = $this->query($sql)) {
            return $this->fetchOne();
        } else {
            return false;
        }
    }

    /**
     * 查询指定SQL 所有记录
     *
     * @param string $sql           SQL查询语句
     * @param boolean $key_field    指定记录结果键值使用哪个字段,默认为 false 使用 i{0...count}
     * @param mixed  $assoc         true 返回数组; false 返回stdClass对象;默认 true
     * @return 失败返回 false
     */
    public function dataTable($sql, $key_field = false, $assoc = true) {
        if (self :: $_result = $this->query($sql)) {
            return $this->fetchAll($key_field, $assoc);
        } else {
            return false;
        }
    }

    /**
     * 取结果(self::$result)中所有记录
     *
     * @param string $keyField      指定记录结果键值使用哪个字段,默认为 false 则使用 i{0...count}
     * @param boolean $assoc        true 返回数组; false 返回stdClass对象;默认 true
     * @return  mixed               没有结果返回 false
     */
    public function fetchAll($keyField = false, $assoc = true) {
        $rows = ($assoc) ? array() : new stdClass;
        $i = -1;
        while ($row = $this->fetchRow(self :: $_result, $assoc)) {
            if ($keyField != false) {
                $i = ($assoc) ? $row[strtoupper($keyField)] : $row -> $keyField; // 前面获取的assoc结果集key转换为大写，因此这里要使用strtoupper
            } else {
                $i++;
            }
            if ($assoc) {
                $rows[$i] = $row;
            } else {
                $rows -> {$i} = $row;
            }
        }
        return ($i > -1) ? $rows : false;
    }

    /**
     * 执行更新数据操作
     *
     * @param string    $table  数据库表名称
     * @param array     $data   待更新的数据
     * @param string    $where  更新条件
     * @return boolean          成功 true; 失败 false
     */
    public function update($table, $data, $where) {
        $set = '';
        if (is_object($data) || is_array($data)) {
            foreach ($data as $k => $v) {
                self :: _formatValue($v);
                $set .= empty($set) ? ("`{$k}` = {$v}") : (", `{$k}` = {$v}");
            }
        } else {
            $set = $data;
        }
        return $this->query("UPDATE `{$table}` SET {$set} WHERE {$where}");
    }

    /**
     * 执行插入数据操作
     *
     * @param   string $table       数据库表名称
     * @param   array $data         待插入的数据
     * @param   string $fields      数据库字段，默认为 null。 为空时取 $data的 keys
     * @return  boolean             成功 true; 失败 false
     */
    public function insert($table, $data, $fields = null) {
        if ($fields == null) {
            foreach($data as $v) {
                if (is_array($v)) {
                    $fields = array_keys($v);
                } elseif (is_object($v)) {
                    foreach($v as $k2 => $v2) {
                        $fields[] = $k2;
                    }
                } elseif (is_array($data)) {
                    $fields = array_keys($data);
                } elseif (is_object($data)) {
                    foreach($data as $k2 => $v2) {
                        $fields[] = $k2;
                    }
                }
                break;
            }
        }
        $_fields = '`' . implode('`, `', $fields) . '`';
        $_data = self :: _formatInsertData($data);
        return $this->query("INSERT INTO `{$table}` ({$_fields}) VALUES {$_data}");
    }

    /**
     * 执行替换数据操作
     *
     * @param   string $table       数据库表名称
     * @param   array $data         待更新的数据
     * @param   string $fields      数据库字段，默认为 null。 为空时取 $data的 keys
     * @return  boolean             成功 true; 失败 false
     */
    public function replace($table, $data, $fields = null) {
        if ($fields == null) {
            foreach($data as $v) {
                if (is_array($v)) {
                    $fields = array_keys($v);
                } elseif (is_object($v)) {
                    foreach($v as $k2 => $v2) {
                        $fields[] = $k2;
                    }
                } elseif (is_array($data)) {
                    $fields = array_keys($data);
                } elseif (is_object($data)) {
                    foreach($data as $k2 => $v2) {
                        $fields[] = $k2;
                    }
                }
                break;
            }
        }
        $_fields = '`' . implode('`, `', $fields) . '`';
        $_data = self :: _formatInsertData($data);
        return $this->query("REPLACE INTO `{$table}` ({$_fields}) VALUES {$_data}");
    }

    /**
     * 更新计数器
     *
     * @param  string   $table  数据库表名称
     * @param  array    $field  待更新的字段名
     * @param  string   $where  更新条件
     * @param  int      $step   增加的步长，默认每次+1
     * @return boolean          成功 true; 失败 false
     */
    public function increase($table, $field, $where, $step = 1) {
        return $this->query("UPDATE `{$table}` SET `{$field}`=`{$field}`+{$step} WHERE {$where}");
    }

    /**
     * 返回最后一次插入的ID
     * @todo 需要修改 这里只当master插入的最后ID
     * return mixed
     */
    public function insertId() {
        return self :: $master -> insert_id;
    }

    /**
     * 执行删除数据操作
     *
     * @param string $table 数据库表名称
     * @param string $where 删除条件,默认为删除整个表数据!!
     * @return boolean      成功 true; 失败 false
     */
    public function delete($table, $where = '') {
        return $this->query("DELETE FROM {$table} ".($where ? " WHERE {$where}" : ''));
    }

    /***
     * *返回结果集数量
     *
     * @param  $result [数据集]
     * @return int
     */
    public function numRows($result = null) {
        if (is_null($result)) $result = self :: $_result;
        return mysqli_num_rows($result);
    }

    /**
     * 统计表记录
     *
     * @param string $table 数据库表名称
     * @param string $where SQL统计条件,默认为查询整个表
     * @return mixed
     */
    public function total($table, $where = '') {
        $sql = "SELECT count(*) FROM {$table} ".($where ? "WHERE {$where}" : '');
        $this->query($sql);
        return $this->fetchOne();
    }

    /**
     * 返回当前查询SQL语句
     * @return string
     */
    public function getSql() {
        return self :: $_sql['sql'];
    }

    /**
     * 返回错误信息
     * @return string
     */
    public function getError() {
        return self :: $_error;
    }
    /**
     * 格式化插入数据
     *
     * @param mixed $data   [array|stdClass] 待格式化的插入数据
     * @return string        insert 中 values 后的 SQL格式
     */
    protected static function _formatInsertData($data) {
        $output = '';
        $is_list = false;
        foreach ($data as $value) {
            if (is_object($value) || is_array($value)) {
                $is_list = true;
                $tmp = '';
                foreach ($value as $v) {
                    self :: format_value($v);
                    $tmp .= !empty($tmp) ? ", {$v}" : $v;
                }
                $tmp = "(" . $tmp . ")";
                $output .= !empty($output) ? ", {$tmp}" : $tmp;
                unset($tmp);
            } else {
                self :: _formatValue($value);
                $output .= !empty($output) ? ", {$value}" : $value;
            }
        }
        if (!$is_list) $output = '(' . $output . ')';
        return $output;
    }

    /**
     * 格式化值
     *
     * @param string  &$value [string] 待格式化的字符串,格式成可被数据库接受的格式
     * @return void
     */
    protected static function _formatValue(&$value) {
        $value = trim($value);
        if ($value === null) {
            $value = 'NULL';
        } elseif (preg_match('/\[\w+\]\.\(.*?\)/', $value)) { // mysql函数 格式:[UNHEX].(参数);
            $value = preg_replace('/\[(\w+)\]\.\((.*?)\)/', "$1($2)", $value);
        } else {
            $value = "'" . addslashes(stripslashes($value)) . "'";
        }
    }

    /**
      * 分析SQL语句，自动分配到主库，从库
      *
      * @param string $sql
      * @return resource
      */
    private function _checkSql($sql){
        $dns = self::$_dns;
        $_n = count($dns);
        if($_n === 1)
        { // 主从一样
            return $this->_connect($dns[0],"master");
        } else { // 主从不同
            $type = substr(trim($sql),0,strpos($sql," ")); // 找SQL语句中的第一个token
            if(strtolower($type) === 'select')
            {
                if($_n === 2) 
                {
                    $sdns = $dns[1];         // 只有两个配置项，而且这里是读取查询，从slave中来执行， 所以使用第二个配置项
                } else {
                    $sdns = $this->_getSlave($dns);
                }
                return $this->_connect($sdns);
            } else {
                return $this->_connect($dns[0],"master");
            }
            
        }
    }

    /**
      * 主从库助手函数
      *
      * @param array $dns
      * @param string $type Slave/Master
      * @return resource
      */
    private function _connect($dns,$type="slave", $charset = 'UTF-8'){
        if(!self::$$type) {
            //Get config of Database
            $_arr = explode('||', $dns);

            $_host = $_arr[0];     //< Mysql host
            $_port = $_arr[1];     //< Mysql port
            $_dbName = $_arr[2];   //< Database name
            $_userName = $_arr[3]; //< User name
            $_userPwd = $_arr[4];  //< User password
            $_charset = str_replace('-', '', $_arr[5]);  //< Charset UTF-8 -> UTF8

            self :: $$type = new mysqli($_host, $_userName, $_userPwd, $_dbName, $_port);
            if (mysqli_connect_errno()) {
                self :: $_error = "Database Connect failed: ". mysqli_connect_error();
                self :: log();
                return false;
            } else {
                self :: $$type -> query("
                    SET character_set_connection=" . $charset .
                    ", character_set_results=" . $charset .
                    ", character_set_client=binary"
                );
            }
        }
        return array("conn"=>self::$$type, "type"=>$type);
    }

    /**
     * 获取slave的算法
     */
    private function _getSlave($dns)
    {
        $_n = count($dns);
        return $dns[mt_rand(1,$_n-1)];
    }
    /**
     * 记录日志
     * @param string $_msg  日志内容
     * @return void
     */
    public static function log($_msg = '') {
        if(!$_msg){
            list($usec, $sec) = explode(' ', microtime());
            $_msg = '[' . date('Y-m-d H:i:s.') . substr($usec, 2, 3) . '][query ' . self :: $_sql['type'] . ' : ' . self :: $_sql['sql'] . '][error: ' . self :: $_error . ']' . PHP_EOL;
        }
        error_log($_msg, 3, self :: $_log);
    }
}
?>