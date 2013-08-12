/**
  * 首页焦点图轮播
  */
HWTrip.Slider.ready(function(){
    $('.sliderbox, .banner').slides({
        container: 'slides_container',
        pagination: true,
        generatePagination: false,
        paginationClass: 'pagination',
        start: 1,
        play: 6000,
        pause: 2000,
        crossfade: false,
        hoverPause: true
    });

    $('.competitive .tith2 p.more a').click(function(){
        $(this).addClass('curr').siblings().removeClass('curr');
        var tagName = $(this).attr('rel');
        $("div[id*='tag-']").hide();
        $('#'+tagName).show();
        return false;
    });

    $("#sidenav").click(function(){
        var ctrl=(navigator.userAgent.toLowerCase()).indexOf('mac')!=-1?'Command/Cmd': 'CTRL';
        if(document.all){
            window.external.addFavorite('http://hwtrip.smartcom.cc', '爱旅-品生活，尚旅游');
        }
        else if(window.sidebar){
            window.sidebar.addPanel('爱旅-品生活，尚旅游', 'http://hwtrip.smartcom.cc', "");
        }else{
            alert('对不起，您的浏览器不支持此操作!\n请您使用菜单栏或Ctrl+D收藏本站。');
        }
        return false;
    })
    
      staticNav();
});

function staticNav() { 
    var sidenavHeight = $("#sidenav").height();
    var winHeight = $(window).height();
    var browserIE6 = (navigator.userAgent.indexOf("MSIE 6")>=0) ? true : false;
    if (browserIE6) {
        var IEleft = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft + 'px';
        var IEtop = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop + 'px';
        $("#sidenav").css({'position' : 'absolute'});
        $("#sidenav").css({'left': IEleft,'top': IEtop});
    } else {
        $("#sidenav").css({'position' : 'fixed'});
    }
    if (sidenavHeight > winHeight) {
        $("#sidenav").css({'position' : 'static'});
    }
}