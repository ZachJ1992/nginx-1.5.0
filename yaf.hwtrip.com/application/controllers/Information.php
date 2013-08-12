<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWAL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: qiaoguoqiang<qiaoguoqiang@huawei.com>                       |
// +----------------------------------------------------------------------+

class InformationController extends Yaf_Controller_Abstract {
    private $_layout;

    public function init(){
        $this->_layout = Yaf_Registry::get('layout');
    }

    public function detailAction($id = 0) {
        //$get = $this->getRequest()->getQuery("get", "default value");
        $model = new InfoModel();
        $detail = $model->detail($id);
        $this->getView()->assign("information", $detail);

        if(!empty($detail['CSS'])){
            $this->_layout->innerCSS = $detail['CSS'];
        }
        if(!empty($detail['JS'])){
            $this->_layout->innerJS = $detail['JS'];
        }

        //$this->_layout->innerCSS = 'p{};'; // TEST ONLY
        //$this->_layout->innerJS = 'var a = 1;'; // TEST ONLY

        $this->getView()->_controller = $this;  //Assign the controller to the view, if you want to, something like this in your action
        //$this->_layout->meta_title = 'A Blog'; // 设置该页的meta title
        $this->_layout->addCSS(
            array(
                array('link' => 'index/index.css'),
            )
        );
        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return TRUE;
    }
}