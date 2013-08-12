<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWAL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: qiaoguoqiang<qiaoguoqiang@huawei.com>                       |
// +----------------------------------------------------------------------+
class LocationModel 
{
    public function getLocations()
    {
        $db = HWAL_DB_Mysql::getInstance();
        $sql = "SELECT city_id, city_name, city_code code, relevance_city, relevance_recommend FROM hwtrip_from_cities";
        $db->query($sql);
        $result = $db->fetchAll();
        return $result;
    }

    public static function getLocactionByCode($code)
    {
        $db = HWAL_DB_Mysql::getInstance();
        $sql = "SELECT city_id, city_name, city_code code, relevance_city, relevance_recommend FROM hwtrip_from_cities WHERE city_code = '$code'";
        $db->query($sql);
        $result = $db->fetchRow();
        return $result;
    }
}