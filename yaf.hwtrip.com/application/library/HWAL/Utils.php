<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWAL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2012 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: qiaoguoqiang<qiaoguoqiang@huawei.com>                       |
// +----------------------------------------------------------------------+

class HWAL_Utils {
    /**
     * 创建像这样的查询: "IN('a','b')";
     *
     * @access   public
     * @param    mix      $itemList      列表数组或字符串
     * @param    string   $fieldName     字段名称
     *
     * @return   void
     */
    public static function dbCreateIN($itemList, $fieldName = ''){
        if (empty($itemList)){
            return $fieldName . " IN ('') ";
        }else{
            if (!is_array($itemList)){
                $itemList = explode(',', $itemList);
            }
            $itemList = array_unique($itemList);
            $itemListTmp = '';
            foreach ($itemList AS $item){
                if ($item !== ''){
                    $itemListTmp .= $itemListTmp ? ",'$item'" : "'$item'";
                }
            }
            if (empty($itemListTmp)){
                return $fieldName . " IN ('') ";
            }else{
                return $fieldName . ' IN (' . $itemListTmp . ') ';
            }
        }
    }
}