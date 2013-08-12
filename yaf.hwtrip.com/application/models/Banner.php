<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWTRIP PHP Framework v1.0                                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2012 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: suozhimin<suozhimin@broadengate.com>                        |
// +----------------------------------------------------------------------+

class BannerModel
{
    public static function getBanners($position = '', $limit = 4)
    {
        $fromCity = Yaf_Registry::get('fromCity');
        $db = HWAL_DB_Mysql::getInstance();
        $sql = 'SELECT focus_image, description, title, focus_thumbnail, focus_url 
            FROM hwtrip_home_focus 
            WHERE focus_status = "1" AND page_type = '.$position.' AND city_code = "'.$fromCity['CODE'].'" 
            ORDER BY sort_order LIMIT '.$limit;
        $db->query($sql);
        $result = $db->fetchAll();
        return $result;
    }
}