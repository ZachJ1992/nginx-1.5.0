<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWAL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: huangjianzhang<huangjianzhang@huawei.com>                   |
// |          wubingjie<wubingjie@huawei.com>                             |
// +----------------------------------------------------------------------+

/*
 * HWAL PHP Framework
 * @category   HWAL
 * @package    HWAL_Cache_Memcache
 */

class HWAL_Cache_Memcache implements HWAL_Cache_Interface {

    /*
     * Object Memcache
     */
    public $_mObject = null;

    /*
     * Separator
     */
    const CST_SPLIT_OR = '||';
    const CST_SPLIT_COLON = ':';


    /* {{{ Method Constructor()
     *
     * Constructor, Init Memcache object
     * @param string $config    Config string,
     *                          e.g.192.168.199.69:11211||192.168.199.69:11212
     * @return  void
     */
    public function __construct($config = '') {
        if (!extension_loaded('memcache')) {
            throw new HWAL_Exception('Do not have memcache support');
        }

        //< Get server variable: HWAL_MEMCACHE_SERVER
        //< from Web server config file.
        if(empty($config)){
            $config = @$_SERVER['HWSL_MEMCACHE_SERVER'];
        }

        //< Format checking
        if(!preg_match('/^\d+(?:\.\d+){3}:\d+(?:\|\|\d+(?:\.\d+){3}:\d+)*$/', $config)){
            throw new HWAL_Exception('The config is wrong');
        }

        //< Init Memcache and add servers
        $this->_mObject = new Memcache;
        foreach(explode(self::CST_SPLIT_OR, $config) as $host){
            $arr = explode(self::CST_SPLIT_COLON, $host);
            $this->_mObject->addServer(
                $arr[0], /* ip */
                $arr[1] /* port */
            );
        }
    }
    /* }}} */

    /* {{{ Method Destructor()
     * Close connection
     */
    public function __destruct()
    {
        $this->_mObject->close();
    }
    /* }}} */

    /* {{{ Method set()
     * Cache a variable in the data store
     *
     * @param   string   $key      Store the variable using this name
     * @param   mixed    $value    The variable to store
     * @param   int      $expire   Time To Live,the default value is 0, it means never expire
     * @return  boolean            TRUE : Success; FALSE : Fail
     */
    public function set($key, $value, $expire=0)
    {
        return $this->_mObject->set(
            $key, $value, false, $expire
        );
    }
    /* }}} */

    /* {{{ Method get()
     * Fetch a stored variable from the cache
     *
     * @param   mixed   $key      The key or array of keys to fetch, two type:
     *                            1、string Memcache::get ( string $key [, int &$flags ] )
     *                            2、array Memcache::get ( array $keys [, array &$flags ] )
     * @return  mixed             Return a stored variable from the cache
     */
    public function get($key)
    {
        return $this->_mObject->get($key);
    }
    /* }}} */

    /* {{{ Method del()
     * Removes a stored variable from the cache
     *
     * @param   string   $key      Store the variable using this name
     * @return  boolean            TRUE : Success; FALSE : Fail
     */
    public function del($key)
    {
        return $this->_mObject->delete($key);
    }
    /* }}} */

    /* {{{ Method inc()
     * Increase a stored number
     *
     * @param   string   $key      The key of the value being increased
     * @param   int      $step     The step, or value to increase
     * @return  mixed              Returns the current value of key's value on success,
     *                             or FALSE on failure
     */
    public function inc($key, $step=1)
    {
        return $this->_mObject->increment($key, $step);
    }
    /* }}} */

    /* {{{ Method dec()
     * Decrease a stored number
     *
     * @param   string   $key      The key of the value being decreased
     * @param   int      $step     The step, or value to decrease
     * @return  mixed              Returns the current value of key's value on success,
     *                             or FALSE on failure
     */
    public function dec($key, $step=1)
    {
        return $this->_mObject->decrement($key, $step);
    }
    /* }}} */

    /* {{{ Method clear()
     * Flush all existing stored items
     *
     * @return  boolean            TRUE : Success; FALSE : Fail
     */
    public function clear()
    {
        return $this->_mObject->flush();
    }
    /* }}} */

    /* {{{ Method stats()
     * Get statistics of the cache
     *
     * @return  array  Return an associative array with cache's statistics
     */
    public function stats()
    {
        return $this->_mObject->getStats();
    }
    /* }}} */
}

