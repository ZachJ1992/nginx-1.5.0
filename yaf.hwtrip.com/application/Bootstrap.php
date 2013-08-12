<?php
/**
 * @name Bootstrap
 * @author qiaoguoqiang
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract{

    public function _initConfig() {
        //把配置保存起来
        $arrConfig = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $arrConfig);
        
        //设置项目中的常用参数
        Yaf_Registry::set('mysqldns', $arrConfig->application->mysqldns);
    }

    /**
     * 实例化并注册HWAL框架插件，个性化相关的实现代码，通过7个钩子实现额外功能
     */
    public function _initHWAL(Yaf_Dispatcher $dispatcher)
    {
        $hwal = new HWALPlugin();
        $dispatcher->registerPlugin($hwal);
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher) {
        //在这里注册自己的路由协议,默认使用简单路由
    }

    /**
     * 添加layout层
     * bootstrap 阶段， request还没有初始化， 希望通过request获取请求的theme参数，这个时候是不可以的
     * 需要在postDispatch阶段， 我们这里在Layout.php的postDispatch hook上实现
     */
/*    public function _initLayout(Yaf_Dispatcher $dispatcher){
        $layout = new LayoutPlugin('layout.phtml');

        // 存储layout引用到Registry中，方便后续调用
        Yaf_Registry::set('layout', $layout);

        // add the plugin to the dispatcher
        $dispatcher->registerPlugin($layout);
    }*/

    public function _initView(Yaf_Dispatcher $dispatcher){
        //在这里注册自己的view控制器
        $smarty = new HWAL_Template_Adapter(APPLICATION_PATH . '/tpl/', array());
        Yaf_Dispatcher::getInstance()->setView($smarty);
    }
}