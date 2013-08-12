<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWAL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: wubingjie<wubingjie@huawei.com>                             |
// +----------------------------------------------------------------------+

/*
 * HWAL PHP Framework
 * @category   HWAL
 * @package    HWAL_Log
 * @usage
 *      1、$log = new HWAL_Log("log.txt");
 *         $log->setLevel(HWAL_Log::CST_LEVEL_ERR);
 *         $log->write("Hello World!", HWAL_Log::CST_LEVEL_DEBUG);
 *      2、HWAL_Log::fileWrite("log.txt", "Hello World!\n");
 * @todo       单实例化
 */

class HWAL_Log {

    /* {{{ Constants */

    //Roll type
    const CST_LOG_TYPE_0 = '';                  //< Normal roll log.
    const CST_LOG_TYPE_1 = '.Ymd';              //< Day roll log.
    const CST_LOG_TYPE_2 = '.Ym';               //< Month roll log.

    //Time format
    const CST_LOG_TFORMAT_0 = '[Y-m-d H:i:s]';  //< Time format 0
    const CST_LOG_TFORMAT_1 = 'Y-m-d H:i:s';    //< Time format 1
    const CST_LOG_TFORMAT_2 = 'YmdHis';         //< Time format 2

    //Log level
    const CST_LEVEL_EMERG   = 0;  //< Emergency: system is unusable
    const CST_LEVEL_ALERT   = 1;  //< Alert: action must be taken immediately
    const CST_LEVEL_CRIT    = 2;  //< Critical: critical conditions
    const CST_LEVEL_ERR     = 3;  //< Error: error conditions
    const CST_LEVEL_WARN    = 4;  //< Warning: warning conditions
    const CST_LEVEL_NOTICE  = 5;  //< Notice: normal but significant condition
    const CST_LEVEL_INFO    = 6;  //< Informational: informational messages
    const CST_LEVEL_DEBUG   = 7;  //< Debug: debug messages
    /* }}} */

    private $_mLogFile = "";    //<Log filename/directory
    private $_mTFormat = self::CST_LOG_TFORMAT_0; //< Log timeformat
    private $_mLevel   = self::CST_LEVEL_ERR; //<  Log level, log will write smaller than this level
    private $_mMaxFile = 0; //< Max log file number.
    private $_mMaxSize = 0; //< Max log file size (Byte).
    private $_mEol = "\n";  //< End char/string of line.

    /**
     * {{{ Method Constructor()
     * Initialize Log.
     *
     * @param   string  $file       Log filename/directory.
     * @param   string  $timeFormat Log timeformat, define in CST_LOG_TFORMAT_x.
     * @param   int     $logLevel   Log level, log will write smaller than this level.
     * @param   int     $maxFile    Max log file number (0 mean no change file, maxsize no use).
     * @param   int     $maxSize    Max log file size (KByte, 0 mean no limit).
     * @param   string  $logType    Log filename type, define in CST_LOG_TYPE_x.
     * @return  int                 0: ok, other: fail.
     */
    public function __construct(
                       $file,
                       $timeFormat = self::CST_LOG_TFORMAT_0,
                       $logLevel   = self::CST_LEVEL_ERR,
                       $maxFile    = 0,
                       $maxSize    = 1024,              //< KByte.
                       $logType    = self::CST_LOG_TYPE_0 )
    {
        $ret = 0;
        if ( strlen($file) <= 0
             || $maxFile < 0
             || $maxSize < 0 ) {
            $ret = -1;
        } else {
            $this->_mLogFile = $file.date( $logType );
            $this->_mTFormat = $timeFormat;
            $this->_mLevel   = $logLevel;
//            $this->_mMaxFile = $maxFile > 0 ? $maxFile : 1;
            $this->_mMaxSize = $maxSize * 1024;   // KByte -> Byte

            if ( $this->_mMaxFile > 0
                        && $maxSize > 0
                        && @filesize($this->_mLogFile) > $this->_mMaxSize ) {
                for ( $i=$this->_mMaxFile; $i>0; $i-- ) {
                    $suffix = ($i == 1) ? ("") : (".".($i-1));
                    @rename( $this->_mLogFile.$suffix, $this->_mLogFile.".".$i );
                } // for
            } // if
            $ret = 0;
        } // if

        return $ret;
    }
    /* }}} */

    /**
     * {{{ Method Destructor()
     */
    public function __destruct()
    {
        //TODO:
    }
    /* }}} */

    /**
     * {{{ Method setLevel()
     * Set log level.
     *
     * @param   int     $level      Log level, log will write smaller than this level.
     * @return  int                 0: ok, other: fail.
     */
    public function setLevel( $level )
    {
        if ( !is_int($level) || $level < 0 ) {
            return -1;
        } else {
            $this->_mLevel   = $level;
            return 0;
        } // if
    }
    /* }}} */

    /**
     * {{{ Method setTimeFormat()
     * Set time format.
     *
     * @param   string  $timeFormat Log timeformat, define in CST_LOG_TFORMAT_x.
     * @return  int                 0: ok, other: fail.
     */
    public function setTimeFormat( $timeFormat )
    {
        if ( strlen($timeFormat) <= 0 ) {
            return -1;
        } else {
            $this->_mTFormat = $timeFormat;
            return 0;
        } // if
    }
    /* }}} */

    /**
     * {{{ Method setMaxFile()
     * Set max log file.
     *
     * @param   int     $maxFile    Max log file number.
     * @return  int                 0: ok, other: fail.
     */
    public function setMaxFile( $maxFile )
    {
        if ( $maxFile <= 0 ) {
            $ret = -1;
        } else {
            $this->_mMaxFile = $maxFile;
            $ret = 0;
        } // if
        return $ret;
    }
    /* }}} */

    /**
     * {{{ Method setMaxSize()
     * Set max size of log file.
     *
     * @param   int     $maxSize    Max log file size (KByte).
     * @return  int                 0: ok, other: fail.
     */
    public function setMaxSize( $maxSize )
    {
        if ( $maxSize <= 0 ) {
            $ret = -1;
        } else {
            $this->_mMaxSize = $maxSize * 1024;   // KByte -> Byte
            $ret = 0;
        } // if
        return $ret;
    }
    /* }}} */

    /**
     * {{{ Method write()
     * Write Log.
     *
     * @param   string  $message    Log message.
     * @param   int     $level      Log level.
     * @return  int                 0: ok, other: fail.
     */
    public function write( $message, $level = self::CST_LEVEL_ERR )
    {
        $ret = 0;
        if ( $level > $this->_mLevel ) {
            $ret = 0;   // Log level lower.
        } else if ( strlen($this->_mLogFile) <= 0
                    || strlen($message) <= 0 ) {
            $ret = -1;
        } else {
            if ( !empty($this->_mTFormat) ) {
                $logmsg = date( $this->_mTFormat )." ".$message;
            } else {
                $logmsg = $message;
            } // if
            $ret = $this->fileWrite( $this->_mLogFile, $logmsg.$this->_mEol );
        } // if
        return $ret;
    }
    /* }}} */

    /**
     * {{{ Method fileWrite()
     * Write message to file. For common writting!
     *
     * @param   string  $file       Log filename/directory.
     * @param   string  $message    Log message.
     * @return  int                 >0: ok, 0: fail.
     */
    public static function fileWrite( $file, $message )
    {
        return file_put_contents($file, $message, FILE_APPEND);
    }
    /* }}} */
}
