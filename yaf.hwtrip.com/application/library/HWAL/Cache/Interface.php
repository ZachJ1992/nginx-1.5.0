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
interface HWAL_Cache_Interface{

    /* {{{ Method set()
     * Cache a variable in the data store
     *
     * @param   string   $key      Store the variable using this name
     * @param   mixed    $value    The variable to store
     * @param   int      $expire   Time To Live,the default value is 0, it means never expire
     * @return  boolean            TRUE : Success; FALSE : Fail
     */
    public function set($key, $value, $expire=0);
    /* }}} */

    /* {{{ Method get()
     * Fetch a stored variable from the cache
     *
     * @param   string   $key      The key used to store the value
     * @return  mixed              Return a stored variable from the cache
     */
    public function get($key);
    /* }}} */

    /* {{{ Method del()
     * Removes a stored variable from the cache
     *
     * @param   string   $key      Store the variable using this name
     * @return  boolean            TRUE : Success; FALSE : Fail
     */
    public function del($key);
    /* }}} */

    /* {{{ Method inc()
     * Increase a stored number
     *
     * @param   string   $key      The key of the value being increased
     * @param   int      $step     The step, or value to increase
     * @return  mixed              Returns the current value of key's value on success,
     *                             or FALSE on failure
     */
    public function inc($key, $step=1);
    /* }}} */

    /* {{{ Method dec()
     * Decrease a stored number
     *
     * @param   string   $key      The key of the value being decreased
     * @param   int      $step     The step, or value to decrease
     * @return  mixed              Returns the current value of key's value on success,
     *                             or FALSE on failure
     */
    public function dec($key, $step=1);
    /* }}} */

    /* {{{ Method clear()
     * Flush all existing stored items
     *
     * @return  boolean            TRUE : Success; FALSE : Fail
     */
    public function clear();
    /* }}} */

    /* {{{ Method stats()
     * Get statistics of the cache
     *
     * @return  array  Return an associative array with cache's statistics
     */
    public function stats();
    /* }}} */
}
?>
