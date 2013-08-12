<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | YAF PHP Framework v1.0                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: qiaoguoqiang<qiaoguoqiang@huawei.com>                       |
// +----------------------------------------------------------------------+

/**
 * 包括URL, IMAGE, CSS, JS等连接的重写构建
 * URL的build应该纳入Router, 后续考虑改写进yaf源代码中
 */
class HWAL_Rewrite
{
    /**
    * URL generating helper intended to generating valid internal hyperlinks.
    * Important: It requires that a constant named BASE_URL be defined.
    *
    * @param string $actionName Optional action name (defaults to current action)
    * @param string $controllerName Optional controller name (defaults to current controller)
    * @param string $moduleName Optional module name (defaults to current module)
    * @param array $params Optional controller parameters
    * @param array $getParams Optional query parameters
    * @param string $target Optional target
    * @return string The generated URL
    */
    public static function url($actionName = NULL, $controllerName = NULL, $moduleName = NULL, array $params = array(), array $getParams = array(), $target = NULL) {

        // Build URL using parts

        $urlParts = array();
        $urlParts[] = Yaf_Registry::get('config')->application['baseUri']; //baseUrl
        $Application = Yaf_Application::app();
        $request = $Application->getDispatcher()->getRequest();
        if ($moduleName === NULL) $moduleName = $request->getModuleName();
        if ($controllerName === NULL) $controllerName = $request->getControllerName();
        if ($actionName === NULL) $actionName = $request->getActionName();

        $moduleName = strtolower($moduleName);
        $controllerName = strtolower($controllerName);
        $actionName = strtolower($actionName);

        // Get default module, controller and action names

        
        $Config = $Application->getConfig();

        $Application->getConfig()->application->dispatcher->defaultModule ? $defaultModule = $Application->getConfig()->application->dispatcher->defaultModule : $defaultModule = 'Index';
        $Application->getConfig()->application->dispatcher->defaultController ? $defaultController = $Application->getConfig()->application->dispatcher->defaultController : $defaultController = 'Index';
        $Application->getConfig()->application->dispatcher->defaultAction ? $defaultAction = $Application->getConfig()->application->dispatcher->defaultAction : $defaultAction = 'index';

        $defaultModule = strtolower($defaultModule);
        $defaultController = strtolower($defaultController);
        $defaultAction = strtolower($defaultAction);

        // Assign module name

        if ($moduleName != $defaultModule) {
            $urlParts[] = strtolower(trim($moduleName, '/')); // To validate a module, inspect its presence in $modules = $Application->getModules();
        }

        // Assign controller name

        if ($actionName != $defaultAction || $controllerName != $defaultController || $moduleName != $defaultModule) {
            $urlParts[] = strtolower(trim($controllerName, '/'));
        }

        // Assign action name

        if ($actionName != $defaultAction) {
            $urlParts[] = strtolower(trim($actionName, '/'));
        }

        // Assign parameters (assumes url parameter pairing)

        foreach ($params as $k => $v) {
            if ($v !== NULL) {
                $urlParts[] = $k;
                $urlParts[] = $v;
            }
        }

        // Assign get parameters

        $getParamsStr = '';
        foreach ($getParams as $k => $v) {
            if (!$getParamsStr) {
                $getParamsStr = '?';
            } else {
                $getParamsStr .= '&';
            }
            $getParamsStr .= rawurlencode($k) . '='. rawurlencode($v);
        }
        if ($getParamsStr) {
            $urlParts[] = $getParamsStr;
        }

        // Build the URL

        $url = implode('/', $urlParts);

        // Assign # target

        if ($target !== NULL) {
            $urlParts = explode('#', $url);
            $url = array_shift($urlParts) . '#' . rawurlencode((string) $target);
        }

        return $url;

    }

    /**
     * 给定theme的layout中的样式和js都是位于$theme指定的目录下面的
     */
    public static function css($css, $theme = 'green')
    {
        return Yaf_Registry::get('config')->application['baseUri'] . "/themes/".$theme."/css/" . $css;
    }
    /**
     * 给定theme的layout中的样式和js都是位于$theme指定的目录下面的
     */
    public static function js($js, $theme = 'green')
    {
        return Yaf_Registry::get('config')->application['baseUri'] . "/themes/".$theme."/js/" . $js;
    }
}