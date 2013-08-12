<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWAL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: qiaoguoqiang<qiaoguoqiang@huawei.com>                       |
// +----------------------------------------------------------------------+

/**
 * HWAL 布局插件
 * 实现yaf对布局的支持
 */
class LayoutPlugin extends Yaf_Plugin_Abstract {
    private $onlybodyhtml   = false;                         // 只显示主题内容，no head block and foot block
    private $theme          = 'green';                       // 主题名称: green为默认
    private $location       = 'shenzhen';                    // 选择城市，默认为深圳shenzhen
    private $externalCSS = array();
    private $externalJS = array();

    private $_layoutDir;
    private $_layoutFile;
    private $_layoutVars =array();


    public function __construct($layoutFile, $layoutDir=null){
        $this->_layoutFile = $layoutFile;
        $this->_layoutDir = ($layoutDir) ? $layoutDir : Yaf_Registry::get('config')->application['themespath'];
        $this->initDefaultLayoutVars(Yaf_Registry::get('config')->layoutVars);
    }

    /**
     * {Yaf Hook}在路由启动的时候被调用
     * 通过get参数初始化layout对象的一些属性:
     *    1) theme : 所使用的主题名称，通过该变量可以找到具体的布局文件，一般布局文件为:/themes/themename/layout.phtml
     *    2) onlybodyhtml : 是否只显示主体HTML,不包含header, footer. 在itravel中嵌套hwtrip的时候，需要判断，一般根据$_GET['sf']判断
     *    3) location : 根据$_GET['location']获取用户所选区域
     * 其中 theme, location选定后，记入session，下次不用在连接中显示出来，保存用户选择。
     * @todo 这里可以完善一些用户主题相关的一些选择
     */
    public function routerStartup ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){
/*        // onlybodyhtml init
        $this->sourceFrom = (isset($_GET['sf']) && ($_GET['sf'] == 1 || $_GET['sf'] == 2))
                            ? true : false;
        // init theme
        $session = Yaf_Session::getInstance();
        $theme = 'green';
        if(isset($_GET['theme']) && preg_replace('[^a-zA-Z0-9-_]', '', $_GET['theme']))
        {
            $theme = preg_replace('[^a-zA-Z0-9-_]', '', $_GET['theme']);
            $session->theme = $theme;
        } else {
            if($session->has('theme'))
            {
                $theme = $session->theme;
            } else {
                $session->theme = $theme;
            }
        }
        $this->theme = $theme;

        // init location
        $location = 'shenzhen';
        if(isset($_GET['location']) && preg_replace('[^a-z]', '', $_GET['location']))
        {
            $location = preg_replace('[^a-z]', '', $_GET['location']);
            $session->location = $location;
        } else {
            if($session->has('location'))
            {
                $location = $session->location;
            } else {
                $session->location = $location;
            }
        }
        $this->location = $location;
        $this->locationData = LocationModel::getLocactionByCode($location);
        */

        $this->onlybodyhtml = Yaf_Registry::get('onlybodyhtml');
        $this->theme = Yaf_Registry::get('theme');
        $this->location = Yaf_Registry::get('location');
        $this->locationData = Yaf_Registry::get('fromCity');
    }

    public function routerShutdown ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){

    }

    public function dispatchLoopStartup ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){

    }

    public function preDispatch ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){
        
    }

    /**
     * {Yaf Hook} 在调度完成时候被调用
     */
    public function postDispatch ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){
        /* get the body of the response */
        $body = $response->getBody();

        /*clear existing response*/
        $response->clearBody();

        /* wrap it in the layout */
        $layout = new Yaf_View_Simple($this->_layoutDir . $this->theme);
        $layout->content = $body;
        $layout->assign('layout', $this->_layoutVars);
        $layout->assign('onlybodyhtml', $this->onlybodyhtml);

        $locationModel      = new LocationModel();
        $naviModel          = new NaviModel();
        $friendLinkModel    = new FriendLinkModel();
        $infoModel          = new InfoModel();
        
        $layout->assign('locations', $locationModel->getLocations());
        $layout->assign('mainNavs', $naviModel->getMainNavi());
        $layout->assign('frindlinks', $friendLinkModel->getFLinks());
        $layout->assign('infolinks', $infoModel->getInfoLinks());

        $layout->assign('css', $this->externalCSS);
        $layout->assign('js', $this->externalJS);

        /* set the response to use the wrapped version of the content */
        $response->setBody($layout->render($this->_layoutFile));
    }

    public function dispatchLoopShutdown ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){

    }

    public function preResponse ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){
        
    }

    public function initDefaultLayoutVars($layoutVars)
    {
        $this->_layoutVars['meta_title'] = $layoutVars['meta_title'];
        $this->_layoutVars['meta_keywords'] = $layoutVars['meta_keywords'];
        $this->_layoutVars['meta_description'] = $layoutVars['meta_description'];
    }
    public function  __set($name, $value) {
        $this->_layoutVars[$name] = $value;
    }
    /** 
     * 添加附加css
     */
    public function addCSS($css = array())
    {
        $this->externalCSS = $css;
    }
    /** 
     * 添加附加css
     */
    public function addJS($js = array())
    {
        $this->externalJS = $js;
    }
}