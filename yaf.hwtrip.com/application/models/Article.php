<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWTRIP PHP Framework v1.0                                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2012 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: suozhimin<suozhimin@broadengate.com>                        |
// +----------------------------------------------------------------------+

class ArticleModel
{
    /**
     * 获取爱旅动态最新5条记录
     */
    public static function getNews($limit = 5)
    {
        return HWAL_DAL::fetchAll('SELECT * FROM hwtrip_information
                                   WHERE `type` = 3 AND information_status = 1 
                                   ORDER BY ordering ASC limit 5');
    }
}