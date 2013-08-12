<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWAL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: qiaoguoqiang<qiaoguoqiang@huawei.com>                       |
// +----------------------------------------------------------------------+
class FriendLinkModel 
{
    public function getFLinks($limit = 10)
    {
        $db = HWAL_DB_Mysql::getInstance();
        if($limit > 0){
            $sql = 'SELECT link_name, link_url
                FROM hwtrip_friend_links
                ORDER BY show_order LIMIT '.$limit;
        }else {
            $sql = 'SELECT link_name, link_url
                FROM hwtrip_friend_links
                ORDER BY show_order';
        }
        $db->query($sql);
        $result = $db->fetchAll();
        return $result;
    }
}