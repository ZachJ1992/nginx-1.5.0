<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWAL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: qiaoguoqiang<qiaoguoqiang@huawei.com>                       |
// +----------------------------------------------------------------------+
class InfoModel 
{
    public function getInfoLinks()
    {
        $db = HWAL_DB_Mysql::getInstance();
        $sql = "SELECT information_id, title, typecode FROM hwtrip_information WHERE  information_status = 1 and type = 0";
        $db->query($sql);
        $result = $db->fetchAll();
        $links = array();
        if(!empty($result)){
            foreach($result as $key => $information){
                if(!empty($information['TYPECODE'])){
                    $links[$key]['URL'] = HWAL_Rewrite::url('detail', 'information', 'index', array('id' => $information['INFORMATION_ID']));
                    $links[$key]['TITLE'] = $information['TITLE'];
                }
            }
        }
        return $links;
    }

    public function detail($id)
    {
        $db = HWAL_DB_Mysql::getInstance();
        $sql = "SELECT title, type, content, js, css, information_status FROM hwtrip_information WHERE information_status = 1 AND information_id =" . (int)$id;
        //echo $sql;
        $db->query($sql);
        $result = $db->fetchRow();
        return $result;
    }
}