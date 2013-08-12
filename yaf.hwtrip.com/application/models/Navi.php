<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWAL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: qiaoguoqiang<qiaoguoqiang@huawei.com>                       |
// +----------------------------------------------------------------------+
class NaviModel 
{
    public function getMainNavi()
    {
        $db = HWAL_DB_Mysql::getInstance();
        $sql = "SELECT channel_name, channel_style, category_id, channel_tips, main_type, channel_url, sort_order, channel_type 
                FROM hwtrip_channel 
                WHERE is_show = '1' ORDER BY sort_order ASC";
        $db->query($sql);
        $result = $db->fetchAll();
        
        $navs = array(
                'bigLeft' => array(
                    array(
                        'CHANNEL_NAME' => '首页',
                        'CHANNEL_STYLE' => '',
                        'CATEGORY_ID'  => '',
                        'CHANNEL_TIPS' => '',
                        'MAIN_TYPE' => '',
                        'CHANNEL_URL' => HWAL_Rewrite::url('index', 'index'),
                        'SORT_ORDER' => 1,
                        'CHANNEL_TYPE' => 'B',
                        'ISACTIVE' => $this->isActiveMenu(HWAL_Rewrite::url('index', 'index')),
                    ),
                ),
                'smallRight' => array(),
            );
        foreach($result as $nav)
        {
            $nav['ISACTIVE'] = $this->isActiveMenu($nav['CHANNEL_URL']);
            if($nav['CHANNEL_TYPE'] == 'B')
            {
                $navs['bigLeft'][] = $nav;
            } else if ($nav['CHANNEL_TYPE'] == 'S') {
                $navs['smallRight'][] = $nav;
            }
        }
        return $navs;
    }

    public function isActiveMenu($givingUrl)
    {
        $request = Yaf_Application::app()->getDispatcher()->getRequest();
        $reqModule = $request->getModuleName();
        $reqController = $request->getControllerName();
        $reqAction = $request->getActionName();
        if(HWAL_Rewrite::url($reqAction, $reqController, $reqModule) == $givingUrl) 
            return true;
        return false;
    }
}