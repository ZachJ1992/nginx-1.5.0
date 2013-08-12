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
 * @package    HWAL_Cache_Apc
 */

class HWAL_Cache_Apc implements HWAL_Cache_Interface {

    /* {{{ Method Constructor()
     *
     * Constructor, Init Apc object
     *
     * @return  void
     */
    public function __construct($config = '') {
        if(!function_exists('apc_cache_info')) {
            throw new HWAL_Exception('Do not have APC support');
        }
        //TODO : $config
    }
    /* }}} */

    /* {{{ Method Destructor()
     *
     */
    public function __destruct()
    {
        //TODO:
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
        return apc_add($key, $value, $expire);
    }
    /* }}} */

    /* {{{ Method get()
     * Fetch a stored variable from the cache
     *
     * @param   string   $key      The key used to store the value
     * @return  mixed              Return a stored variable from the cache
     */
    public function get($key)
    {
        return apc_fetch($key);
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
        return apc_delete($key);
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
        return apc_inc($key, $step);
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
        return apc_dec($key, $step=1);
    }
    /* }}} */

    /* {{{ Method clear()
     * Flush all existing stored items
     *
     * @return  boolean            TRUE : Success; FALSE : Fail
     */
    public function clear()
    {
        //< If cache_type is "user", the user cache will be cleared; otherwise,
        //< the system cache (cached files) will be cleared.
        return apc_clear_cache('user');
    }
    /* }}} */

    /* {{{ Method stats()
     * Get statistics of the cache
     *
     * @return  array  Return an associative array with cache's statistics
     */
    public function stats()
    {
        //< 1. If cache_type is "user", information about the user cache will be returned.

        //< 2. If cache_type is "filehits", information about which files have been served
        //<    from the bytecode cache for the current request will be returned. This feature
        //<    must be enabled at compile time using --enable-filehits .
        //< 3. If an invalid or no cache_type is specified, information about the system
        //<    cache (cached files) will be returned.
        return apc_cache_info('user');
    }
    /* }}} */
}
