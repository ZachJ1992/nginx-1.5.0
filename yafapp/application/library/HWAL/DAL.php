<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWAL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: qiaoguoqiang<qiaoguoqiang@huawei.com>                       |
// +----------------------------------------------------------------------+

class HWAL_DAL
{
    /**
     * 获取给定sql语句的所有结果集
     * @param string $sql : 给定的SQL语句
     * @param string $keyField : 指定记录结果键值使用哪个字段,默认为 false 则使用 i{0...count}
     * @param bool $assoc : 返回联系数组的形式还是对象的形式，默认为true，返回联系数组形式
     * @return array | object
     * @usage  
     *    HWAL_DAL::fetchAll($sql) : 返回assoc array, key为0,1,2,...n
     *    HWAL_DAL::fetchAll($sql, 'fieldName') : 返回联系数组，key为fieldName对应的值, 其中fieldName为结果集SELECT中的其中之一
     *    HWAL_DAL::fetchAll($sql, 'fieldName', false) : 返回对象
     */
    public static function fetchAll($sql, $keyField = false, $assoc = true)
    {
        $db = HWAL_DB_Mysql::getInstance();
        $db->query($sql);
        return $db->fetchAll($keyField, $assoc);
    }

    /**
     * 获取一行数据记录
     *
     * @param   string  $sql    SQL语句
     * @param   bool  $assoc    是否返回联系数组
     * @return  array           查询结果集
     */
    public static function fetchRow($sql, $assoc = true)
    {
        $db = HWAL_DB_Mysql::getInstance();
        $result = $db->query($sql);
        return $db->fetchRow($result, $assoc);
    }

    /**
     * 查询指定SQL 第一行，第一列 值
     *
     * @param string $sql   SQL查询语句
     * @return mixed        失败返回 false
     */
    public static function dataOne($sql)
    {
        $db = HWAL_DB_Mysql::getInstance();
        return $db->dataOne($sql);
    }

    /**
     * 执行插入数据操作
     *
     * @param   string $table       数据库表名称
     * @param   array $data         待插入的数据
     * @param   string $fields      数据库字段，默认为 null。 为空时取 $data的 keys
     * @return  boolean             成功返回插入记录的ID; 失败 false
     * @todo 返回的上一次插入ID
     */
    public static function insert($table, $data, $fields = null)
    {
        $db = HWAL_DB_Mysql::getInstance();
        $res = $db->insert($table, $data, $fields);
        return $res ? $db->insertId() : false;
    }

    /**
     * 执行更新数据操作
     *
     * @param string    $table  数据库表名称
     * @param array     $data   待更新的数据
     * @param string    $where  更新条件
     * @return boolean          成功 true; 失败 false
     */
    public static function update($table, $data, $where)
    {
        $db = HWAL_DB_Mysql::getInstance();
        return $db->update($table, $data, $where);
    }

    /**
     * 原生态的sql执行
     *
     * $sql sql语句
     */
    public static function query($sql)
    {
        $db = HWAL_DB_Mysql::getInstance();
        return $db->query($sql);
    }

    /**
     * 删除数据记录
     *
     * @param   string  $table  表名
     * @param   string  $where  删除条件
     * @return  bool            成功 true; 失败 false
     */
    public static function delete($table, $where = '')
    {
        $db = HWAL_DB_Mysql::getInstance();
        return $db->delete($table, $where);
    }

    public static function replace($table, $data, $fields = null)
    {
        $db = HWAL_DB_Mysql::getInstance();
        return $db->replace($table, $data, $fields);
    }

    /**
     * 增加计数器
     *
     * @param   string  $table  表名
     * @param   array   $field  计数器字段名
     * @param   string  $where  更新条件
     * @param   int     $step   每次增加多少，默认每次+1
     * @return  bool            成功 true; 失败 false
     */
    public static function increase($table, $field, $where, $step = 1) {
        $db = HWSL_Db::factory(self::$dbconfig);
        return $db->increase($table, $field, $where, $step);
    }
}