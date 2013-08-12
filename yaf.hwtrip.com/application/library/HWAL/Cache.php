<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWAL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: huangjianzhang<huangjianzhang@huawei.com>                   |
// +----------------------------------------------------------------------+

/*
 * HWAL PHP Framework
 * @category   HWAL
 * @package    HWAL_Cache
 * @usage
 *  e.g.
 *
 *  $cache = new HWAL_Cache(HWAL_Cache::CST_MEMCACHE);
 *  $cache = $cache->getInstance();
 *  $cache->set('key', 'hello world');
 *  print_r($cache->get('key'));
 *  print_r($cache->stats());
 *
 */

class HWAL_Cache {

    /* {{{ Constants  */
    const CST_APC = 0x01;       //< For APC
    const CST_MEMCACHE = 0x02;  //< For Memcache
    /* }}} */

    private $_mType = 0;        //< Cache engine
    private $_mConfig = '';     //< Config string

    /** 
     * {{{ Method Constructor()
     *
     * Constructor
     * @param int $type    Cache engine :
     *                     1. HWAL_Cache:CST_MEMCACHE
     *                     2. HWAL_Cache:CST_APC
     * @param string $config    Config string //< For Memcache
     * @return  void
     */
    public function __construct($type = self::CST_APC, $config = '')
    {
        $this->_mType = $type;
        $this->_mConfig = $config;
    }
    /* }}} */

    /** 
     * {{{ Method getInstance()
     * Object Factory
     * @return object
     */
    public function getInstance()
    {
        switch($this->_mType){
            case self::CST_APC:{
                return new HWAL_Cache_Apc($this->_mConfig);
            }
            case self::CST_MEMCACHE:{
                return new HWAL_Cache_Memcache($this->_mConfig);
            }
            default:{
                throw new HWAL_Exception('Cache configuration type is error!');
            }
        }
        return NULL;
    }
    /* }}} */

    /** 
     * {{{ Method Destructor()
     *
     */
    public function __destruct()
    {
        //TODO:
    }
    /* }}} */
}
?>