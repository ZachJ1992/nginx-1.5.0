<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWSL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: qiaoguoqiang<qiaoguoqiang@huawei.com>                       |
// +----------------------------------------------------------------------+

class HWAL_Template_Adapter implements Yaf_View_Interface
{
    /**
     * HWAL_Template object
     * @var HWAL_Template
     */
    public $_template;
    public $_template_dir = '';
    /**
     * Constructor
     * 初始化模板类，模板路径，以及相应assign的参数
     * @param string $tmplPath
     * @param array $extraParams
     * @return void
     */
    public function __construct($tmplPath = null, $extraParams = array()) {
        $this->_template = new HWAL_Template;
 
        if (null !== $tmplPath) {
            $this->setScriptPath($tmplPath);
        }
 
        foreach ($extraParams as $key => $value) {
            $this->_template->$key = $value;
        }
    }
 
    /**
     * Assign variables to the template
     *
     * Allows setting a specific key to the specified value, OR passing
     * an array of key => value pairs to set en masse.
     *
     * @see __set()
     * @param string|array $spec The assignment strategy to use (key or
     * array of key => value pairs)
     * @param mixed $value (Optional) If assigning a named variable,
     * use this as the value.
     * @return void
     */
    public function assign($spec, $value = null) {
        if (is_array($spec)) {
            $this->_template->assign($spec);
            return;
        }
 
        $this->_template->assign($spec, $value);
    }

    /**
     * Processes a template and returns the output.
     *
     * @param string $name The template to process.
     * @return string The output.
     */
    public function render( $name, $tpl_vars = null ) {
        return $this->_template->fetch($this->getScriptPath() . '/' .$name);
    }

    public function display ( $view_path, $tpl_vars = NULL )
    {
        return $this->_template->fetch($this->getScriptPath().'/'.$view_name);//返回应该输出的内容,而不是显示
    }
    public function setScriptPath($view_directory)
    {
        return $this->_template_dir = $view_directory;
    }
    public function getScriptPath()
    {
        return $this->_template_dir;
    }
}
?>