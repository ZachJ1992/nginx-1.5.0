<?php
/**
 * @name SamplePlugin
 * @desc Yaf定义了如下的6个Hook,插件之间的执行顺序是先进先Call
 * @see http://www.php.net/manual/en/class.yaf-plugin-abstract.php
 * @author qiaoguoqiang
 */
class HWALPlugin extends Yaf_Plugin_Abstract {
    var $cacheMkey = '';                           // 保存请求页面的缓存key
    var $cacheExclude = false;                     // 当前请求是否无需缓存
    // 在路由之前触发
    public function routerStartup ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){
        require(APPLICATION_PATH . '/conf/configure.php');
    }

    // 路由结束之后触发
    public function routerShutdown ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){
        // 路由结束， 这时候可以根据请求对象， $_GET, $_POST获取请求页面的缓存
        // 'SiteCache.' . $request->getRequestUri() . '.' .md5(serialize($_GET).serialize($_POST)) 作为key
        /*
        if($this->cacheExclude){
            $cacheData = $cache->get($key);
            if($cacheData) {
                echo $cacheData;
                exit();
            }
        }
        ob_start();
         */
    }

    // 分发循环开始之前被触发
    public function dispatchLoopStartup ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){

    }

    // 如果在一个请求处理过程中, 发生了forward, 则这个事件会被触发多次
    public function preDispatch ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){
        
    }

    // 此时动作已经执行结束, 视图也已经渲染完成. 和preDispatch类似, 此事件也可能触发多次
    public function postDispatch ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){

    }

    // 所有的业务逻辑都已经运行完成, 但是响应还没有发送
    public function dispatchLoopShutdown ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){
        /*
        $re = ob_get_contents();
        ob_end_clean();
        echo $re;
        $cache->set($this->SiteCache_mkey, $re);
        */
    }

    public function preResponse ( Yaf_Request_Abstract $request , Yaf_Response_Abstract $response ){
        
    }
}
