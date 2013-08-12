<?php
/**
 * @name IndexController
 * @author qiaoguoqiang
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Yaf_Controller_Abstract {
    /** 
     * 默认动作
     */
    public function indexAction() {
        $this->getView()->assign('destinationNavs', DestinationModel::getDestSideMenus());   // 左侧目的地导航数据
        $this->getView()->assign('banners', BannerModel::getBanners(0));                     // 首页主banner数据
        $this->getView()->assign('centerBanners', BannerModel::getBanners(100));             // 首页中间长条banner数据
        $this->getView()->assign('news', ArticleModel::getNews());                           // 爱旅动态数据

        $this->getView()->assign('homeRecommend', ProductModel::getRecommend(Yaf_Registry::get('location')));
        $this->getView()->_controller = $this;
        return TRUE;
    }
}