<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWTRIP PHP Framework v1.0                                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2012 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: suozhimin<suozhimin@broadengate.com>                        |
// +----------------------------------------------------------------------+

class ProductModel
{
    const CST_PRODUCT_TRASH                   = 0; //回收站产品
    const CST_PRODUCT_ONSALE                  = 1; //在售产品
    const CST_PRODUCT_WAIT_FOR_APPROVAL       = 2; //等待审批产品
    const CST_PRODUCT_NOT_APPROVED            = 3; //审批不通过的产品
    const CST_PRODUCT_APPROVED_NOT_ONSALE     = 4; //审批通过的产品，暂没有上架
    const CST_PRODUCT_NEW                     = 5; //新加产品， 暂未提审
    
    //产品价格类型
    const CST_PRICE_TYPE_NORMAL               = 0; //正常价格
    const CST_PRICE_TYPE_TRIP                 = 1; //旅游产品价格
    const CST_PRICE_TYPE_TICKET               = 2; //门票类型价格
    const CST_PRICE_TYPE_WHOLESALE            = 3; //批发类型价格
    
    const CST_GO_CITIES                       = 4; //目的城市id
    const CST_GO_TICKET_CITIES                = 19; //门票目的城市id
    const CST_FROM_CITIES                     = 1; //出发城市id
    const CST_GO_DATE                         = 2;  //行程天数
    const CST_PRODUCT_TAGS                    = 25;  //产品标签 
    
    public static $recommendType = array(
            '0' => '本季推荐',
            '1' => '门票',
            '3' => '海外旅游推荐',
            '4' => '港澳旅游推荐',
            '5' => '国内旅游推荐',
            '6' => '品鉴之旅推荐',
            '7' => '精品出境游推荐'
    );

    public static $sonType = array(
            '0' => '马代',
            '1' => '东南亚',
            '2' => '海岛',
            '3' => '港澳',
            '4' => '欧洲',
            '5' => '澳美',
    );
    /**
     * 根据用户所在区域获取首页推荐的产品列表
     */
    public static function getRecommend($location = 'shenzhen')
    {
        $recommendType = self::$recommendType;
        $sql = "SELECT r.id, r.trip_id,r.recommend_type,r.son_type,r.sort_order,p.product_name ,p.product_alias, p.base_price, p.cost_price, p.brief, p.product_icon, p.viewcnt, s.cover_image, s.recommend_image
            FROM hwtrip_recommend r,hwtrip_products p,hwtrip_pictures s
            WHERE r.trip_id = p.id AND p.id = s.trip_id AND r.city_code = '{location}'
            ORDER BY r.son_type,r.sort_order ASC";

        $ret = HWAL_DAL::fetchAll(str_replace('{location}', $location, $sql));
        if(empty($ret) && $location != 'shenzhen'){ // 如果选择的地区没有配置推荐，且不为shenzhen则，默认显示深圳的推荐产品
            $ret = HWAL_DAL::fetchAll(str_replace('{location}', 'shenzhen', $sql));
        }
        $recommend = array();
        if(!empty($ret)){
            foreach($ret as $key => $value){
                $value['SPRODUCT_NAME'] = HWAL_String::mbSubstr($value['PRODUCT_NAME'], 0, 15);
                $value['LOW_ADULT_PRICE'] = self::getLowAdultPrice($value['TRIP_ID']);
                $value['BASE_PRICE'] = empty($value['LOW_ADULT_PRICE'])? isset($value['BASE_PRICE'])&&!empty($value['BASE_PRICE'])?$value['BASE_PRICE'] : '已下线': $value['LOW_ADULT_PRICE']['ADULT_PRICE'];
                if($value['RECOMMEND_TYPE'] != '7'){
                    $recommend[$recommendType[$value['RECOMMEND_TYPE']]][] = $value;
                }else{
                    $recommend[$recommendType[$value['RECOMMEND_TYPE']]][$value['SON_TYPE']][] = $value;
                }
                
            }
        }
        
        //获得用户地区，筛选周边推荐，出发城市属性为4
        if(!empty($location)) {
            $areaList = LocationModel::getLocactionByCode($location);
            //周边城市关联
            $relevanceArea = '';
            if(!empty($areaList['RELEVANCE_CITY'])){
                $relevanceAreaArr = DestinationModel::getRelevanceCity($areaList['RELEVANCE_CITY']);
                if(sizeof($relevanceAreaArr) > 0){
                    foreach($relevanceAreaArr as $value){
                        $relevanceArea .= "'".$value['CITY_NAME']."',";
                    }
                }
                $relevanceArea = trim($relevanceArea, '\',');
                
                $sql = "SELECT product_id 
                    FROM hwtrip_product_attr
                    WHERE hwtrip_product_attr.attr_id = ".self::CST_GO_CITIES." AND " . HWAL_Utils::dbCreateIN($relevanceArea, 'attr_value');
                $productIdArr = HWAL_DAL::fetchAll($sql);
                if(!empty($productIdArr)){
                    $productId = '';
                    if(sizeof($relevanceAreaArr) > 0){
                        foreach($productIdArr as $value){
                            $productId .= $value['PRODUCT_ID'].",";
                        }
                    }
                    $productId = trim($productId, ',');
                    $sql = 'SELECT s.trip_id,p.product_name ,p.product_alias , p.base_price, p.brief, s.cover_image, s.recommend_image
                        FROM hwtrip_products p,hwtrip_pictures s
                        WHERE p.id = s.trip_id AND ' . HWAL_Utils::dbCreateIN($productId, 'p.id');
                    $relevanceRecomend = HWAL_DAL::fetchAll($sql);
                    if(!empty($ret)){
                        $recommend['周边推荐'] = $relevanceRecomend;
                    }
                }
                
            }
        }

        if(isset($recommend['精品出境游推荐']) && sizeof($recommend['精品出境游推荐'])){
            $areaSort = array_keys($recommend['精品出境游推荐']);
            $sonType = self::$sonType;
            $i = 0;
            foreach ($recommend['精品出境游推荐'] as $key => $value) {
                unset($recommend['精品出境游推荐'][$key]);
                $recommend['精品出境游推荐']['content'][$i] = $value;
                $i++;
            }
            $recommend['精品出境游推荐']['area_sort'] = $areaSort;
        }
        return $recommend;
    }

    //获取一般产品所对应的有效日期中最低的成人价和所对应的日期，房型
    public static function getLowAdultPrice($tripID){
        $sql = 'SELECT housetype_id from hwtrip_trip_price WHERE trip_id = ' .(int)$tripID . ' order by housetype_id desc ';
        $housetypeId = HWAL_DAL::fetchRow($sql);
        if(isset($housetypeId['HOUSETYPE_ID']) && !empty($housetypeId['HOUSETYPE_ID'])){
            $sql = 'SELECT p.adult_price , d.price_date, h.name, p.housetype_id
                FROM hwtrip_trip_price p, hwtrip_trip_price_date d, hwtrip_housetype h
                WHERE p.trip_id = '.(int)$tripID.' AND p.housetype_id 
                = h.id AND p.id = d.price_id AND d.price_date >= '.strtotime(date("Y-m-d 00:00:00")) . '
                ORDER BY p.adult_price asc, d.price_date asc, h.id asc ';
        }else{
            $sql = 'SELECT p.adult_price , d.price_date, p.housetype_id
                FROM hwtrip_trip_price p, hwtrip_trip_price_date d
                WHERE p.trip_id = '.(int)$tripID.' AND p.id = d.price_id AND d.price_date >= '.strtotime(date("Y-m-d 00:00:00")) . '
                ORDER BY p.adult_price asc, d.price_date asc ';
        }
        $result = HWAL_DAL::fetchRow($sql);
        return $result;
    }
}