<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWTRIP PHP Framework v1.0                                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2012 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: suozhimin<suozhimin@broadengate.com>                        |
// +----------------------------------------------------------------------+

class DestinationModel
{
    public static function getDestSideMenus()
    {
        $cityGroups = HWAL_DAL::fetchAll('SELECT id,name,icon_name FROM hwtrip_city_group ORDER BY sort_order ASC');
        $cities = HWAL_DAL::fetchAll('SELECT city_id,city_name,city_code,group_id 
                                      FROM hwtrip_destination_cities
                                      WHERE is_search = 1 AND group_id <> 0
                                      ORDER BY sort_order ASC, city_id desc');
        $recommend = array();
        //周边城市

        foreach($cityGroups as $key => $value){
            switch($value['ICON_NAME']){
                case 0://海岛游
                    $value['ICON'] = '<span class="ico-island"></span>';
                    break;
                case 1://港澳游图标
                    $value['ICON'] = '<b class="hkm"></b>';
                    break;
                case 2://国内旅游图标
                    $value['ICON'] = '<span class="ico-surrounding"></span>';
                    break;
                case 3://出境游图标
                    $value['ICON'] = '<span class="ico-abroad"></span>';
                    break;
                case 4://目的地图标
                    $value['ICON'] = '<span class="ico-recommend"></span>';
                    break;
                default:
                    break;
            }
            $recommend[$value['ID']] = $value;
        }
        
        if(!empty($cities)){
            foreach($cities as $key => $value){
                    $recommend[$value['GROUP_ID']]['DATA'][] = $value;
            }
        }
        foreach($recommend as $key => $value){
            //截取8个元素给展示，其余隐藏
            if(isset($value['DATA']) && !empty($value['DATA'])){
                $recommend[$key]['DATA'] = array_slice($value['DATA'],0,6);
                $recommend[$key]['SHOWBOX'] = array_slice($value['DATA'],6);
            }
        }
        rsort($recommend);
        return $recommend;
    }

    /**
     * 获取周边城市
     */
    public static function getRelevanceCity($area){
        $sql = 'SELECT city_name,city_id,city_code
            FROM hwtrip_destination_cities
            WHERE ' . HWAL_Utils::dbCreateIN($area, 'city_id');
        return HWAL_DAL::fetchAll($sql);
    }
}