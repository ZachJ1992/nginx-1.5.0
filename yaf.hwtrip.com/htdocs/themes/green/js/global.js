/**
 * HWTrip Object
 */
if (typeof(HWTrip) == "undefined" || !HWTrip) {
    var HWTrip= {};
    HWTrip.version = "2.0.0";
    HWTrip.domainUrl = $('#jqscript').attr('hosturl');
};
/* {{{ 全局数据配置 */
HWTrip.Config = {
    defaultDataCharacterSet : "utf-8",
    domainDefault : HWTrip.domainUrl,
    domainUrl : "",
    jqueryPluginsUrl : HWTrip.domainUrl + "js/plugins/",
    jqueryUIDateCSSUrl : HWTrip.domainUrl + "static/css/common/jquery.ui.datepicker.css", //@todo need modify for new version
    keyCode : {KEYUP:38, KEYDOWN:40, ENTER:13, TAB:9}
};
/* }}} */

/* {{{ Cookie操作类 */
HWTrip.Cookie = (function(){
    var _domain = HWTrip.Config.domainDefault;

    //读取Cookie
    function _get(name){
        var r = new RegExp("(?:^|;+|\\s+)" + name + "=([^;]*)");
        var m = document.cookie.match(r);
        return (!m ? "" : m[1]);
    };

    //设置Cookie
    function _set(name, value, domain, path, hour){
        if (hour) {
            var today = new Date();
            var expire = new Date();
            expire.setTime(today.getTime() + 3600000 * hour);
        }
        document.cookie = name + "=" + value + "; " + (hour ? ("expires=" + expire.toGMTString() + "; ") : "") + (path ? ("path=" + path + "; ") : "path=/; ") + (domain ? ("domain=" + domain + ";") : ("domain=" + _domain + ";"));
        return true;
    };

    //删除Cookie
    function _del(name, domain, path){
        document.cookie = name + "=; expires=Mon, 26 Jul 1997 05:00:00 GMT; " + (path ? ("path=" + path + "; ") : "path=/; ") + (domain ? ("domain=" + domain + ";") : ("domain=" + _domain + ";"));
        return true;
    };

    return {
        get : _get,
        set : _set,
        del : _del
    };
})();
/* }}} */

/* {{{ 本地存储接口 */
/* Copyright (c) 2010-2011 Marcus Westin */
/* https://github.com/marcuswestin/store.js/blob/master/store.js */
// 用法
// HWTrip.Storage.set(key, val);
// HWTrip.Storage.get(key);
// HWTrip.Storage.remove(key);
// HWTrip.Storage.clear();
HWTrip.Storage = (function(){
    var api = {},
        win = window,
        doc = win.document,
        localStorageName = 'localStorage',
        globalStorageName = 'globalStorage',
        namespace = '__storejs__',
        storage;

    api.disabled = false
    api.set = function(key, value) {}
    api.get = function(key) {}
    api.remove = function(key) {}
    api.clear = function() {}
    api.transact = function(key, transactionFn) {
        var val = api.get(key)
        if (typeof val == 'undefined') { val = {} }
        transactionFn(val)
        api.set(key, val)
    }

    api.serialize = function(value) {
        return JSON.stringify(value)
    }
    api.deserialize = function(value) {
        if (typeof value != 'string') { return undefined }
        return JSON.parse(value)
    }

    // Functions to encapsulate questionable FireFox 3.6.13 behavior
    // when about.config::dom.storage.enabled === false
    // See https://github.com/marcuswestin/store.js/issues#issue/13
    function isLocalStorageNameSupported() {
        try { return (localStorageName in win && win[localStorageName]) }
        catch(err) { return false }
    }

    function isGlobalStorageNameSupported() {
        try { return (globalStorageName in win && win[globalStorageName] && win[globalStorageName][win.location.hostname]) }
        catch(err) { return false }
    }

    if (isLocalStorageNameSupported()) {
        storage = win[localStorageName]
        api.set = function(key, val) { storage.setItem(key, api.serialize(val)) }
        api.get = function(key) { return api.deserialize(storage.getItem(key)) }
        api.remove = function(key) { storage.removeItem(key) }
        api.clear = function() { storage.clear() }
    } else if (isGlobalStorageNameSupported()) {
        storage = win[globalStorageName][win.location.hostname]
        api.set = function(key, val) { storage[key] = api.serialize(val) }
        api.get = function(key) { return api.deserialize(storage[key] && storage[key].value) }
        api.remove = function(key) { delete storage[key] }
        api.clear = function() { for (var key in storage ) { delete storage[key] } }
    } else if (doc.documentElement.addBehavior) {
        var storage = doc.createElement('div')
        function withIEStorage(storeFunction) {
            return function() {
                var args = Array.prototype.slice.call(arguments, 0)
                args.unshift(storage)
                // See http://msdn.microsoft.com/en-us/library/ms531081(v=VS.85).aspx
                // and http://msdn.microsoft.com/en-us/library/ms531424(v=VS.85).aspx
                doc.body.appendChild(storage)
                storage.addBehavior('#default#userData')
                storage.load(localStorageName)
                var result = storeFunction.apply(api, args)
                doc.body.removeChild(storage)
                return result
            }
        }
        api.set = withIEStorage(function(storage, key, val) {
            storage.setAttribute(key, api.serialize(val))
            storage.save(localStorageName)
        })
        api.get = withIEStorage(function(storage, key) {
            return api.deserialize(storage.getAttribute(key))
        })
        api.remove = withIEStorage(function(storage, key) {
            storage.removeAttribute(key)
            storage.save(localStorageName)
        })
        api.clear = withIEStorage(function(storage) {
            var attributes = storage.XMLDocument.documentElement.attributes
            storage.load(localStorageName)
            for (var i=0, attr; attr = attributes[i]; i++) {
                storage.removeAttribute(attr.name)
            }
            storage.save(localStorageName)
        })
    }

    try {
        api.set(namespace, namespace)
        if (api.get(namespace) != namespace) { api.disabled = true }
        api.remove(namespace)
    } catch(e) {
        api.disabled = true
    }

    return api
})();
/* }}} */

/* {{{ 装载jQuery插件 */
HWTrip.jQueryPlugin = (function(){
    return {
        //加载外部jQuery插件
        load : function(name, callback){
            //回调函数
            var _call = $.isFunction(callback)?callback : (function(){});
            //单实例,只加载一遍插件文件
            if( !!window['_jPlugin_Loaded_' + name] ){ return  _call(); }
            $.ajaxSetup({cache: true});
            $.getScript(HWTrip.Config.jqueryPluginsUrl+name+'.js', function() {
                window['_jPlugin_Loaded_' + name] = true;
                _call();
            });
            return true;
        }
    };
})();
/* }}} */


/* {{{ 模板操作类
//用法参考 : jQuery Templating Plugin
//HWTrip.Template.ready(function(){
//  var movies = [
//      { Name: "The Red Violin", ReleaseYear: "1998" },
//      { Name: "Eyes Wide Shut", ReleaseYear: "1999" },
//      { Name: "The Inheritance", ReleaseYear: "1976" }
//  ];
//  $( "#movieTemplate" ).tmpl( movies )
//  .appendTo( "#movieList" );
//});
*/
HWTrip.Template = (function() {
return { ready : function(callback){
    HWTrip.jQueryPlugin.load('template', callback);
}};
})();
/* }}} */

/* {{{ 图片轮播类
//eg : HWTrip.Slider.ready(function(){
//  这里写Slider插件的代码！！
//  用法参考 : http://slidesjs.com
//});
*/
HWTrip.Slider = (function() {
    return { ready : function(callback){
        HWTrip.jQueryPlugin.load('slider', callback);
    }};
})();
/* }}} */

/* {{{ 全局数据配置 */
HWTrip.User = (function(){
    var userID = HWTrip.Cookie.get('hwtrip_uid');
    var email = HWTrip.Cookie.get('email');
    function _init(){
        (function(){
            var c_cookieId = HWTrip.Cookie.get('cookieId');
            //如果cookie中不存在cookieId,则进行设置
            if(c_cookieId.length==0)
            {
                var dateObj=new Date();
                var cookieId=dateObj.getTime()+Math.ceil(Math.random()*100000).toString();
                //设置保存1天，根据需求更改
                HWTrip.Cookie.set('cookieId', cookieId, 'smartcom.cc', '/', 30);
            }
        })();
        //userID = 1; //for test
        //email = 'qiaoguoqiang@huawei.com';
        if(userID != ''){
            //取出email中的用户名
            //email = email.split('%40');
            //email = email[0];
            $('.loginM').hide();
            $('.anonyM').show();
            $('.ucenter').text('我的爱旅');
        }else{
            $('.loginM').show();
            $('.anonyM').hide();
            $('.ucenter').text('');
        }
    }
    function _logout(){
        $.ajax({
            url : HWTrip.domainUrl + 'user/logout.html',
            dataType : 'json',
            data : r=Math.random(),
            success : function(res){
                if(res['ResultCode'] == 0){
                    location.href = HWTrip.domainUrl + 'user/login.html';
                }else{//退出失败，@todo

                }
            }
        });
    }
    return {
        init : _init,
        logout : _logout
    }
})();

/* {{{ 工具类 */
HWTrip.Util = (function(){
    var rules = {
            'username' : /^[a-zA-Z\s\u4e00-\u9fa5]+$/,
            'usernamewithnum' : /^[0-9a-zA-Z\s\u4e00-\u9fa5]+$/,
            //'email' : /^[\w#\$%'\*\+\-\/=\?\^`{}\|~]+([.][\w!#\$%'\*\+\-\/=\?\^`{}\|~]+)*@[-a-z0-9]{1,20}[.][a-z0-9]{1,10}([.][a-z]{2})?$/i,
            'email' : /^([a-zA-Z0-9]+[_|\-|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\-|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/,
            'phone' : /^(?:\d{3,4})?-?(?:\d{7,8})(?:-\d{1,})?$/,
            'mobile' : /^1(?:3|5|8)\d{9}$/,
            'chinese' : /.*[\u4e00-\u9fa5]+.*$/,
            'partyusername' : /^([A-Za-z]{2,9}|[\u4e00-\u9fa5]{2,5})$/ ,
            'partychinese' : /^[\u4e00-\u9fa5]{2,4}$/,
            'partybus' : /^[3-6S\u4e00-\u9fa5]{2,11}[12]?$/,
            'qq' : /^[1-9][0-9]{4,}$/,
            'idcard' : /^(\d{15}|(?:\d{17}[\dxX]))$/,
            'postcode' : /^[1-9][0-9]{5}$/,
            'cardno' : /^[0-9a-zA-Z]+$/,
            'date' : /^(\d{4})-(\d{2})-(\d{2})$/,
            'number' : /^[1-9][0-9]*$/,
            'numberwithzero' : /^[0-9]*$/,
            'districtcode' : /^[0-9]{2,4}$/,
            'idcard15' : /^(\d{6})()?(\d{2})(\d{2})(\d{2})(\d{3})$/,
            'idcard18' : /^(\d{6})()?(\d{4})(\d{2})(\d{2})(\d{3})([0-9xX])$/,
            'creditcard' : function(cardNo) {
                var reMasterCard = /^5[1-5]\d{14}$/;
                var reVisa = /^4\d{12}\d{3}?$/;
                var reOther = /^6\d{15}$/
                //TODO 后面记得改回
                if (reMasterCard.test(cardNo)) {
//                    return _luhuCheck(cardNo);
                    return true;
                } else if(reVisa.test(cardNo)) {
//                    return _luhuCheck(cardNo);
                    return true;
                } else if(reOther.test(cardNo)) {
                    return true;
                } else {
                    return false;
                }
            },
            'psgname':function(name) {
                if (/^[\u4e00-\u9fa5]+[a-zA-Z]*$/.test(name)) { //中文名，包含生僻字情况
                    return true;
                } else if (/^(?:[a-zA-Z]+\/[a-zA-Z]+){1}(?:\s[a-zA-Z]+)*$/.test(name)) { //英文名
                    return true;
                } else {
                    return false;
                }
            },
            'english':/^[A-Za-z]+$/
    };
    /* {{{ 检查数据的合法性 */
    function _match(name, val, param) {
        var rule = rules[name];
        if (rule == undefined || rule == null) {return false;}
        return $.isFunction(rule) ? rule(val, param) : rule.test(val);
    }
    /* }}}} */
    /* {{{ 打印JSON */
    function _debug(theObj) {
        var retStr = '';
        if (typeof theObj == 'object') {
            retStr += '<div style="font-family:Tahoma; font-size:7pt; text-align:left;">';
            for (var p in theObj) {
                if (typeof theObj[p] == 'object') {
                    retStr += '<div><b>['+p+'] => ' + typeof(theObj) + '</b></div>';
                    retStr += '<div style="padding-left:25px;">' + _debug(theObj[p]) + '</div>';
                } else {
                    retStr += '<div>['+p+'] => <b>' + theObj[p] + '</b></div>';
                }
            }
            retStr += '</div>';
        }
        $('body').append(retStr);
    }
    /* }}} */

    /* {{{ 去掉focus时的虚线边 */
    function _killFocus(options) {
        var options = options || ['A', 'BUTTON'];
        $.each(options, function(idx, val) {
            $(val).focusin(function(o) {
                $(this).blur();
            });
        });
    }
    /* }}} */

    /* {{{ 判断IE6的最优方法
     * @author zhanhailiang
     */
    function _isIE6() {
        return !-[1,]&&!window.XMLHttpRequest;
    }
    /* }}} */

    /* {{{ 判断给定值是否为空
     * @author qiaoguoqiang
     */
    function _isEmpty(val){
        switch (typeof(val)){
            case 'string':
                return $.trim(val).length == 0 ? true : false;
                break;
            case 'number':
                return val == 0;
                break;
            case 'object':
                return val == null;
                break;
            case 'array':
                return val.length == 0;
                break;
            default:
                return true;
        }
    }
    /* }}} */

    function _isInt(val){
        if (val == ""){
            return false;
        }
        var reg = /\D+/;
        return !reg.test(val);
    }

    /* {{{ 获取URL GET参数 */
    function _getUrlParams(param) {
        var paraObj = {};
        if(location.search !== "") {
            var queryArray = location.search.substring(1).split("&");
            var len = queryArray.length;
            for(var i = 0; i < len; i++) {
                var match = queryArray[i].match(/([^=]+)=([^=]+)/);
                if(match !== null) {
                    paraObj[match[1]] = match[2];
                }
            }
        }
        if (param != undefined) {
            return paraObj[param] == undefined ? '' : paraObj[param];
        } else {
            return paraObj;
        }
    }
    /* }}} */

    /* {{{ URL decode */
    function _urlDecode(url) {
        if (typeof(decodeURIComponent) != "undefined")
            return decodeURIComponent(url);
        return url;
    }
    /* }}} */

    return {
        match : _match,
        debug : _debug,
        killFocus : _killFocus,
        gerUrlParams : _getUrlParams,
        urlDecode : _urlDecode,
        isIE6 : _isIE6,
        isInt : _isInt
    }
})();
/* }}} */

/* {{{ 弹出框 */
//用法：
//HWTrip.Dialog.showMsg({
//  type:'success , fail, warning , confirm , 成功， 失败， 警告， 疑问', 标题的icon
//  dialogTitle:'弹出窗口的标题',
//  title:'内容标题', 为空则不会显示整个标题栏，包括icon
//  content:'内容',
//  width:500, 自定义宽度
//  btn:false, 是否显示底部的按钮
//  yesFn:function(){alert('点击确定后的callback，在实现类似confirm的功能时可用此参数');},
//  closeFn:function{alert('点击关闭后的callback');}
//});
HWTrip.Dialog = (function(){
var dialogWrapper, dialogMaskWrapper = null;
function _showMsg(options) {
// 选项
var _configs = {
    type : 'success', //success , fail, warning , confirm , 成功， 失败， 警告， 疑问
    title : '',
    content : '',
    dialogTitle : '温馨提示',
    width : '430',
    closeFn : null,
    margin : '0px 0px 0px 0px',
    callback : null,
    mask : true,
    newTpl : false,
    tplID : ''
};
$.extend(_configs, options);

// 模板
if(_configs.newTpl && _configs.tplID != ''){//使用新模板
    var template;
    HWTrip.Template.ready(function(){
        template = $('#'+_configs.tplID).tmpl().html();
    });
}else{
    var _confirm = '';
    if(_configs.type == 'confirm'){
        _confirm = '<div class="op"><a href="javascript:;" class="btn-blue-auto" id="_Dialog_yes"><b>确&nbsp;定</b></a><a href="javascript:;" class="btn-blue-auto" id="_Dialog_no"><b>取&nbsp;消</b></a></div>';
    }
    var template = ['<div id="_Dialog_showMsg" class="pop" style="width:480px;">',
                        '<div class="pop-box">',
                            '<div class="pop-tit">',
                                '<h3>{DIALOG_TITLE}</h3>',
                                '<span class="bt-close" id="_Dialog_close"><a href="javascript:;" title="关闭">关闭</a></span>',
                            '</div>',
                            '<div class="pop-con pop-pad">',
                                (_configs.content != '') ? '{CONTENT}' : '<div class="pop-text"><b class="ico-{TYPE}33"></b><h4>{TITLE}</h4></div>',
                            _confirm + '</div>',
                        '</div>',
                        '<div style="position:absolute;z-index:-1;left:0px;top:0;width:100%;">',
                        '<iframe style="width:100%;height:197px;filter:alpha(opacity=0);-moz-opacity:0"></iframe>',
                    '</div>'].join('');

    // 替换内容
    template = template.replace('{TYPE}', _configs.type).replace('{DIALOG_TITLE}', _configs.dialogTitle).replace('{TITLE}', _configs.title).replace('{CONTENT}', _configs.content);
}

if(_configs.tplID == 'reviews'){//点评模板
    var template = ['<div class="pop">',
                        '<div class="pop-box">',
                            '<div class="pop-tit"><h3>线路点评</h3><span class="bt-close" id="_Dialog_close"><a href="javascript:;" title="关闭">关闭</a></span></div>',
                            '<div class="pop-con">',
                                '<form method="post" id="reviews">',
                                '<div class="pop-con-innr">',
                                    '<div class="chks">',
                                        '<ul class="clearfix">',
                                            '<li>',
                                                '<p class="p1">行&nbsp;程</p>',
                                                '<p class="p2"><span><input name="route" type="radio" value="20"/>非常满意</span><span><input name="route" type="radio" value="15"/>满意</span><span><input name="route" type="radio" value="10"/>一般</span><span><input name="route" type="radio" value="5"/>不满意</span><span><input name="route" type="radio" value="0"/>非常不满意</span><span id="route"></span></p>',
                                            '</li>',
                                            '<li>',
                                                '<p class="p1">食&nbsp;宿</p>',
                                                '<p class="p2"><span><input name="accommodation" type="radio" value="20"/>非常满意</span><span><input name="accommodation" type="radio" value="15"/>满意</span><span><input name="accommodation" type="radio" value="10"/>一般</span><span><input name="accommodation" type="radio" value="5"/>不满意</span><span><input name="accommodation" type="radio" value="0"/>非常不满意</span><span id="accommodation"></span></p>',
                                            '</li>',
                                            '<li>',
                                                '<p class="p1">交&nbsp;通</p>',
                                                '<p class="p2"><span><input name="traffic" type="radio" value="20"/>非常满意</span><span><input name="traffic" type="radio" value="15"/>满意</span><span><input name="traffic" type="radio" value="10"/>一般</span><span><input name="traffic" type="radio" value="5"/>不满意</span><span><input name="traffic" type="radio" value="0"/>非常不满意</span><span id="traffic"></span></p>',
                                            '</li>',
                                            '<li>',
                                                '<p class="p1">导&nbsp;游</p>',
                                                '<p class="p2"><span><input name="tourist_guide" type="radio" value="20"/>非常满意</span><span><input name="tourist_guide" type="radio" value="15"/>满意</span><span><input name="tourist_guide" type="radio" value="10"/>一般</span><span><input name="tourist_guide" type="radio" value="5"/>不满意</span><span><input name="tourist_guide" type="radio" value="0"/>非常不满意</span><span id="tourist_guide"></span></p>',
                                            '</li>',
                                            '<li>',
                                                '<p class="p1">预定过程</p>',
                                                '<p class="p2"><span><input name="booking_process" type="radio" value="20"/>非常满意</span><span><input name="booking_process" type="radio" value="15"/>满意</span><span><input name="booking_process" type="radio" value="10"/>一般</span><span><input name="booking_process" type="radio" value="5"/>不满意</span><span><input name="booking_process" type="radio" value="0"/>非常不满意</span><span id="booking_process"></span></p>',
                                            '</li>',
                                        '</ul>',
                                        '<p class="caption"><span>注：非常满意=20分</span><span>满意=15分</span><span>一般=10分</span><span>不满意=5分</span><span>非常不满意=0分</span></p>',
                                        '<p><strong>意见</strong></p>',
                                        '<p class="textinput"><textarea name="review" cols="" rows=""></textarea></p>',
                                    '</div>',
                                    '<div class="op"><input type="button" name="viewsbtn" id="viewsbtn" value="提交" class="btn-blue-b"><input type="hidden" name="productID" id="productID" value="" /><input type="hidden" name="userID" id="userID" value="" /></div>',
                                '</div>',
                                '</form>',
                            '</div>',
                      '</div>',
                    '</div>'].join('');
}

// 创建弹出框
if (dialogWrapper) {_closeMsg();}
dialogWrapper = $(template).appendTo($('body')).css({width:_configs.width+'px'}).hide();
$('.pop-con', dialogWrapper).css('margin', _configs.margin);
if (!_configs.title) {$('#_Dialog_showMsg .pop-con h4').remove();}
if (!_configs.dialogTitle) {$('#_Dialog_showMsg .pop-tit').remove();}

$('#_Dialog_close').click(function(){
    if ($.isFunction(_configs.closeFn)) {
        _configs.closeFn.call();
        _closeMsg();
    } else {
        _closeMsg();
    }
});
$('#_Dialog_no').click(function(){
    if ($.isFunction(_configs.closeFn)) {
        _configs.closeFn.call();
        _closeMsg();
    } else {
        _closeMsg();
    }
});
$('#_Dialog_yes').click(function(){
    if ($.isFunction(_configs.yesFn)) {
        _configs.yesFn.call();
        _closeMsg();
    } else {
        _closeMsg();
    }
});
//可移动
var offsetX = 0;
var offsetY = 0;
var bool = false;
$('.pop-tit').mousedown(function(event){
    bool = true;
    offsetX = event.pageX - parseInt(dialogWrapper.css("left"));
    offsetY = event.pageY - parseInt(dialogWrapper.css("top"));
    $(this).css('cursor','move');
}).mouseup(function(){
    bool=false;
}).mousemove(function(event){
    if(!bool){
        return;
    }
    var x = event.pageX-offsetX;
    var y = event.pageY-offsetY;
    dialogWrapper.css("left", x);
    dialogWrapper.css("top", y);
})

//定位到中间
var _isIE6 = !-[1,]&&!window.XMLHttpRequest;
var _ww = $(window).width();
var _wh = $(window).height();
var _ow = dialogWrapper.width();
var _oh = dialogWrapper.height();
var _docLeft = $(document).scrollLeft();
var _docTop = _isIE6 ? $(document).scrollTop() : 0;

var _left = ((_ww - _ow) / 2 + _docLeft) + 'px';
var _top = ((_wh - _oh) / 2 + _docTop) + 'px';
dialogWrapper.css({'left':_left, 'top':_top, position:_isIE6 ? 'absolute' : 'fixed', 'z-index':'1002'});

//遮罩
if (_configs.mask) {
    dialogMaskWrapper = $('<div id="_Dialog_maskwrap"></div>');
    var mask = $('<div id="_Dialog_mask"></div>');
    var domTxt = '(document).documentElement';
    //完美解决IE6不支持position:fixed的bug
    var ie6Css = _isIE6 ?
            'position:absolute;left:expression(' + domTxt + '.scrollLeft);top:expression('
            + domTxt + '.scrollTop);width:expression(' + domTxt
            + '.clientWidth);height:expression(' + domTxt + '.clientHeight)'
        : '';
    if (_isIE6) {
        mask.html(
                '<iframe src="about:blank" style="width:100%;height:100%;position:absolute;' +
                'top:0;left:0;z-index:-1;filter:alpha(opacity=0);"></iframe>');
    }
    mask.attr('style', 'height:100%;background:#000;filter:alpha(opacity=50);opacity:0.3;');
    dialogMaskWrapper.attr('style', 'width:100%;height:100%;position:fixed;z-index:1001;top:0;left:0;overflow:hidden;'+ie6Css);
    dialogMaskWrapper.append(mask).appendTo('body');
}

//显示弹出框
dialogWrapper.show();
if ($.isFunction(_configs.callback)) {
    _configs.callback.call();
}
}

function _closeMsg() {
dialogWrapper.remove();
if (dialogMaskWrapper) {
    dialogMaskWrapper.remove();
}
}
return {showMsg : _showMsg, closeMsg : _closeMsg}
})();
/* }}} */

HWTrip.Common = (function() {
  //< 分享到显示
    var _shareTo = function(){
        $(".shareto").live("click",function(){
            var $share_b = $(this).parent().siblings(".share-b");
            if($share_b.is(":visible")){
                $(this).removeClass("shareto-on");
                $share_b.fadeOut(500);
            }else{
                $share_b.fadeIn(500);
                $(this).addClass("shareto-on");
            }
            return false;
        });
    };
    //< 订阅模块
    var _subscribe = function() {

        $('#php-email').bind({
            click: function() {
                $('#php-subscribe-email').find('.php-error-tip').hide();
                if( $.trim($(this).val()) === '请输入Email') {
                    $(this).val('');
                }
            },
            blur: function() {
                $('#php-subscribe-email').find('.php-error-tip').hide();
                if( $.trim($(this).val()) === '') {
                    $(this).val('请输入Email');
                }
            }
        });

        $('#php-subscribe-email').submit(function() {
            var email = $.trim($('#php-email').val());
            var pos = $('#php-email').position();
            if(email === '' || email === '请输入Email') {
                $('#php-subscribe-email').find('.php-error-tip').remove();
                $('#php-subscribe-email').append('<div class="tooltip php-error-tip" id="error-php-email"><div class="tooltip-t"></div><div class="tooltip-c">请输入邮箱地址</div></div>');
                $('#error-php-email').css({left: pos.left, top: pos.top - 35});
                return false;
            } else if(!HWTrip.Util.match('email', email)) {
                $('#php-subscribe-email').find('.php-error-tip').remove();
                $('#php-subscribe-email').append('<div class="tooltip php-error-tip" id="error-php-email"><div class="tooltip-t"></div><div class="tooltip-c">您输入的邮箱格式有误</div></div>');
                $('#error-php-email').css({left: pos.left, top: pos.top - 35});
                return false;
            } else {
                $.ajax({
                    url : HWTrip.Config.domainDefault + 'boxes/subscribe.php',
                    type : 'post',
                    dataType : 'json',
                    data : $(this).serialize(),
                    success : function(data) {
                        if(data['ResultCode'] == 0) {
                            HWTrip.Dialog.showMsg({
                                type : 'success',
                                title: '您的订阅已经成功',
                                closeFn: function() {
                                    $('#php-email').val('').trigger('blur');
                                }
                            });
                        } else if(data['ResultCode'] == 2) {
                            HWTrip.Dialog.showMsg({
                                type : 'fail',
                                title: '对不起，您已订阅过'
                            });
                        } else if(data['ResultCode'] === '10002') {
                            HWTrip.Dialog.showMsg({
                                type: 'warning',
                                title: '对不起，您提交过于频繁'
                            });
                        } else {
                            HWTrip.Dialog.showMsg({
                                type : 'fail',
                                title: '对不起，提交失败，请重新提交'
                            });
                        }
                    }
                });
            }

            return false;
        });
    };
    var _init = function() {
        //< 分享到显示
        _shareTo();
        //< 订阅模块
        _subscribe();
    };

    return {
        init : _init
    }
})();

/* {{{ listtable功能 */
HWTrip.ListTable = (function(){

    var _url = location.href.lastIndexOf("?") == -1 ? location.href.substring((location.href.lastIndexOf("/")) + 1) : location.href.substring((location.href.lastIndexOf("/")) + 1, location.href.lastIndexOf("?"));
    var sf = HWTrip.Util.gerUrlParams('sf');
    if(sf != ''){
        _url += "?is_ajax=1&sf=" + sf;
    }else{
        _url += "?is_ajax=1";
    }
    var _filter = new Object;
    _filter.page = 1;
    var _loadListDivId = 'listDiv',
    _pageSize = '#pageSize',
    _totalPages = '#totalPages';
    _filter.pageCount = $(_totalPages).html();
    //alert(_filter.pageCount);
    
    /**
     * 切换排序方式
     */
    function _sort(sortBy, sortOrder){
        var args = "sortBy="+sortBy+"&sortOrder=";

        if (this.filter.sortBy == sortBy){
            args += this.filter.sortOrder == "DESC" ? "ASC" : "DESC";
        }else{
            args += "DESC";
        }
        for (var i in this.filter){
            if (typeof(this.filter[i]) != "function" && i != "sortOrder" && i != "sortBy" && !HWCMS.Util.isEmpty(this.filter[i])){
                args += "&" + i + "=" + this.filter[i];
            }
        }

        this.filter['pageSize'] = this.getPageSize();
        $.ajax({
            url : this.url,
            type : 'post',
            dataType : 'json',
            data : args,
            success : this.listCallback
        });
    }

    /**
     * 翻页
     */
    function _gotoPage(page){
        if(page != null)
            this.filter['page'] = page;
        if(this.filter['page'] > this.filter.pageCount)
            this.filter['page'] = 1;
        this.filter['pageSize'] = this.getPageSize();
        this.loadList();
    }


    /**
     * 载入列表
     */
    function _loadList(){
        var args = this.compileFilter();

        var _this = this;
        $.ajax({
            url : this.url,
            type : 'post',
            dataType : 'json',
            data : args,
            success : this.listCallback,
            context : _this
        });
    }

    /**
     * 获取每页显示数目
     */
    function _getPageSize(){
        var ps = 20;
        var pageSize = $(this.pageSizeId);

        if (pageSize){
            ps = HWTrip.Util.isInt(pageSize.text()) ? parseInt(pageSize.text()) : 20;
        }
        return ps;
    }

    /**
     * 删除列表中的一个记录
     */
    function _remove(id, cfm, opt){
        if (opt == null){
            opt = "remove";
        }
        var _this = this;
        HWTrip.Dialog.showMsg({
            type: 'confirm',
            title: '确认修改？',
            yesFn: function(){
                var args = "act=" + opt + "&id=" + id + _this.compileFilter();
                $.ajax({
                    url : _this.url,
                    type : 'get',
                    dataType : 'json',
                    data : args,
                    success : _this.listCallback
                });
            }
        });
    }

    function _changeStatus(id, changeto, cfm, opt){
        if (opt == null){
            opt = "changeStatus";
        }
        var _this = this;
        HWTrip.Dialog.showMsg({
            type: 'confirm',
            title: '确认修改？',
            yesFn: function(){
                var args = "act=" + opt + "&id=" + id + '&changeto=' + changeto + _this.compileFilter();
                $.ajax({
                    url : _this.url,
                    type : 'get',
                    dataType : 'json',
                    data : args,
                    success : _this.listCallback
                });
            }
        });
    }

    function _compileFilter(){
        var args = '';
        for (var i in this.filter){
            if (typeof(this.filter[i]) != "function" && typeof(this.filter[i]) != "undefined"){
                args += "&" + i + "=" + encodeURIComponent(this.filter[i]);
            }
        }
        return args;
    }

    function _listCallback(response){
        if (response['resultCode'] != '0'){
            alert(response['description']);
        }else{
            try{
                if(this.loadListDivId == null){
                    this.loadListDivId = 'listDiv';
                }
                document.getElementById(this.loadListDivId).innerHTML = response['content'];
                //$('#listDiv').html(response['content']);
                if (typeof response.filter == "object"){
                    this.filter = response.filter;
                    this.pageCount = response.filter.pageCount;
                }
            }catch (e){
                alert(e.message);
            }
        }
    }

    function _gotoPageFirst(){
        if (this.filter.page > 1){
            this.gotoPage(1);
        }
    }

    function _gotoPagePrev(){
        if (this.filter.page > 1){
            this.gotoPage(this.filter.page - 1);
        }
    }

    function _gotoPageNext(){
        if (this.filter.page < this.filter.pageCount){
            this.gotoPage(parseInt(this.filter.page) + 1);
        }
    }

    function _gotoPageLast(){
        if (this.filter.page < this.filter.pageCount){
            this.gotoPage(this.filter.pageCount);
        }
    }

    function _changePageSize(e){
        var evt = HWCMS.Util.fixEvent(e);
        if (evt.keyCode == 13){
            this.gotoPage();
            return false;
        };
    }
    function _setListDivId(id){
        _loadListDivId = id;
    }
    function _setUrl(url){
        _url = url;
    }
    function _setPageSizeId(pageSize){
        _pageSize = pageSize;
    }
    function _setTotalPagesId(totalPages){
        _totalPages = totalPages;
    }
    function _init(){
        this.filter.pageCount = $(_totalPages).html();
    }


    return {
        url : _url,
        loadListDivId : _loadListDivId,
        pageSizeId : _pageSize,
        totalPages : _totalPages,
        filter : _filter,
        remove : _remove,
        sort : _sort,
        loadList : _loadList,
        gotoPage : _gotoPage,
        gotoPageFirst : _gotoPageFirst,
        gotoPagePrev : _gotoPagePrev,
        gotoPageNext : _gotoPageNext,
        gotoPageLast : _gotoPageLast,
        getPageSize: _getPageSize,
        changePageSize : _changePageSize,
        changeStatus : _changeStatus,
        compileFilter : _compileFilter,
        listCallback : _listCallback,
        setUrl : _setUrl,
        setListDivId : _setListDivId,
        setPageSizeId : _setPageSizeId,
        setTotalPagesId : _setTotalPagesId,
        init : _init
    }
})();
/* }}} */


/* {{{ 收藏 */
HWTrip.Favorite = (function(){
    function _init(elem){
        $(elem).live('click',_bindFavorite);
    }
    function _bindFavorite(e){
        var favID = $(this).attr('id');
        var tripID = favID.replace(/favorite/g, '');
        $.ajax({
            url : HWTrip.Config.domainDefault + 'boxes/favorite.php?',
            type : 'post',
            dataType : 'json',
            data : 'tripID='+tripID,
            success : function(data) {
                if(data['ResultCode'] == 0) {
                    HWTrip.Dialog.showMsg({
                        type : 'success',
                        title: '收藏成功!',
                        closeFn: function() {
                            $('#php-email').val('').trigger('blur');
                        }
                    });
                } else if(data['ResultCode'] == 1) {
                    HWTrip.Dialog.showMsg({
                        type : 'fail',
                        title: '你已经收藏该旅行！'
                    });
                } else if(data['ResultCode'] == -2) {
                    location.href = HWTrip.Config.domainDefault+'user/login.html';
                } else {
                    HWTrip.Dialog.showMsg({
                        type : 'fail',
                        title: '收藏失败，请重试！'
                    });
                }
            }
        });
    }
    return {
        init : _init
    };
})();
/* }}} */

/* {{{ 分享*/
HWTrip.Mailto = (function(){
    function _init(elem){
        $(elem).live('click',_bindMailto);
    }
    function _bindMailto(e){
        var elemID = $(this).attr('id');
        var tripID = elemID.replace(/mailto/g, '');
        var pos = $(this).offset();
        var scrollTop = $(window).scrollTop();
        var documentLeft = $(document).scrollLeft();
        var pos_x = (( $(window).width()) - ( $(this).outerWidth())) * 0.6 + documentLeft;
        var pos_y = (( $(window).height()) - ( $(this).outerHeight())) * 0.25 + scrollTop;
        $.ajax({
            url : HWTrip.Config.domainDefault + 'boxes/share.php',
            type : 'post',
            dataType : 'json',
            data : 'tripID='+tripID,
            success : function(data) {
                if(data['ResultCode'] == 0) {
                    var tripData = data['Data'];
                    var sendtoTpl = ['<div class="pop" style="width:498px; display: block; left: '+ pos_x +'px; top: '+ pos_y +'px; margin-left:-350px; position: absolute; z-index: 1002;">',
                                     '    <div class="pop-box">',
                                     '    <div class="pop-tit"><h3>分享旅游给朋友</h3><span class="bt-close"><a href="javascript:;" onclick="$(\'#sendFriendPop\').hide();" title="关闭">关闭</a></span></div>',
                                     '    <div class="pop-con">',
                                     '    <div class="pop-con-innr">',
                                     '    <div class="pic-tx">',
                                     '    <img alt=""  width="122" height="78" src="'+ HWTrip.Config.domainDefault + tripData.NEWSLETTER_IMAGE+'">',
                                     '    <h3>'+tripData.PRODUCT_NAME+'</h3>',
                                     '    <p class="p1">'+tripData.BRIEF+'</p>',
                                     '    <p class="p2"><strong class="yuan">&yen;'+tripData.BASE_PRICE+'</strong></p>',
                                     '    </div>',
                                     '    </div>',
                                     '    <div class="email-elm clearfix">',
                                     '    <form id="send2friendForm">',
                                     '    <div class="f1">',
                                     '    <h4>信息</h4>',
                                     '    <p><textarea rows="" cols="" name="recommendInfo" id="recommendInfo">这条旅游线路不错，我猜你肯定喜欢......</textarea></p>',
                                     '    </div>',
                                     '    <div class="f2">',
                                     '    <h4>您的邮箱</h4>',
                                     '    <p><input type="text" value="email@example.com" name="yourEmail" class="input-text foc" id="yourEmail"></p>',
                                     '    <h4>朋友邮箱地址</h4>',
                                     '    <p><textarea rows="" cols="" name="friendEmail" id="friendEmail" maxlength="1000">输入朋友的邮箱(多个请回车)</textarea></p>',
                                     '    <div class="op"><a href="javascript:;" title="发送邮件" class="btn-blue-b" id="sendBtn">发送邮件</a></div>',
                                     '    </div>',
                                     '    <input type="hidden" name="sendTripID" id="sendTripID" value="'+tripData.ID+'" />',
                                     '    </form>',
                                     '    </div>',
                                     '    </div>',
                                     '    </div>',
                                     '    </div>',
                                     '    </div>'].join('');
                    $('#sendFriendPop').html(sendtoTpl).show();
                    $('#yourEmail').live({
                        'focus':function(){
                            $(this).removeClass('err-red');
                             if($(this).val() == 'email@example.com'){
                                 $(this).val('').addClass('foc');
                             }else{
                                 $(this).addClass('foc');
                             }
                        },
                        'blur':function(){
                            if($(this).val() == ''){
                                $(this).val('email@example.com').removeClass('foc');
                            }
                        }
                    });
                    $('#friendEmail').live({
                        'focus':function(){
                            $(this).removeClass('err-red');
                            if($(this).val() == '输入朋友的邮箱(多个请回车)'){
                                $(this).val('').addClass('foc');
                            }else{
                                $(this).addClass('foc');
                            }
                        },
                        'blur':function(){
                            if($(this).val() == ''){
                                $(this).val('输入朋友的邮箱(多个请回车)').removeClass('foc');
                            }
                        }
                    });
                    $('#recommendInfo').live({
                        'focus':function(){
                            $(this).removeClass('err-red');
                            if($(this).val() == '这条旅游线路不错，我猜你肯定喜欢......'){
                                $(this).val('').addClass('foc');
                            }else{
                                $(this).addClass('foc');
                            }
                        },
                        'blur':function(){
                            if($(this).val() == ''){
                                $(this).val('这条旅游线路不错，我猜你肯定喜欢......').removeClass('foc');
                            }
                        }
                    });
                    $('#sendBtn').live('click',function(){
                        var sampleEmail = 'email@example.com';
                        var tips = '这条旅游线路不错，我猜你肯定喜欢……';
                        var friendset = '输入朋友的邮箱(多个请回车)';
                        var $num = 1, hasError = false;
                        var sendEmailFillError = false;
                        var hasRightSendEmail = false;
                        var $friendEmails = new Array();
                        var friendEmail = $.trim($('#friendEmail').val());
                        if(friendEmail == '' || friendEmail == friendset){
                            hasError = true;
                            $('#friendEmail').addClass('err-red');
                        }else{
                            $friendEmails = friendEmail.split('\n');
                            $num = $friendEmails.length;
                            for(var i=0; i<$friendEmails.length; i++){
                                if($friendEmails[i] != sampleEmail && HWTrip.Util.match('email', $friendEmails[i])){
                                    hasRightSendEmail = true;
                                }else{
                                    sendEmailFillError = true;
                                    $('#friendEmail').addClass('err-red');
                                    alert('请填写正确的邮箱！');
                                }
                            }
                        }

                        if(!hasRightSendEmail || (hasRightSendEmail && sendEmailFillError)){
                            return false;
                        }

                        var $yourEmail = $.trim($('#yourEmail').val());
                        if($yourEmail == '' || $yourEmail == sampleEmail){
                            hasError = true;
                            $('#yourEmail').addClass('err-red');
                        }else if(!HWTrip.Util.match('email', $yourEmail)){
                            hasError = true;
                            $('#yourEmail').addClass('err-red');
                        }

                        var $recommendInfo = $.trim($('#recommendInfo').val());
                        if($recommendInfo == '' || $recommendInfo == tips){
                            hasError = true;
                            $('#recommendInfo').addClass('err-red');
                        }

                        var $data = {
                            sendTripID : $.trim($('#sendTripID').val()),
                            emailNum : $num,
                            friendEmails : $friendEmails,
                            yourEmail : $yourEmail,
                            recommendInfo : $recommendInfo
                        };

                        if(!hasError){
                            $.ajax({
                                url:HWTrip.Config.domainDefault + 'boxes/send2friend.php',
                                type:'post',
                                dataType:'json',
                                data:$data,
                                success:function(response){
                                    $('#sendFriendPop').hide();
                                    if(response['ResultCode'] == '10002'){
                                        HWTrip.Template.ready(function(){
                                            HWTrip.Dialog.showMsg({
                                                type : 'warning',
                                                title: '提交过于频繁'
                                            });
                                        });
                                    }else{
                                        HWTrip.Dialog.showMsg({
                                            type:'success',
                                            dialogTitle:'分享成功',
                                            title:'分享成功'
                                        });
                                    }
                                }
                            });
                        }
                    });
                } else if (data['ResultCode'] == 1) {
                    HWTrip.Dialog.showMsg({
                        type : 'fail',
                        title: '你已经收藏该旅行！'
                    });
                } else {
                    HWTrip.Dialog.showMsg({
                        type : 'fail',
                        title: '收藏失败，请重试！'
                    });
                }
            }
        });
    }
    return {
        init : _init
    };
})();
/* }}} */



if (typeof(HWTrip) == "undefined" || !HWTrip) {
    var HWTrip= {};
};
HWTrip.Basic = (function(){
    //按钮hover状态
    var _btnHover = function(){
        $('.btn-green-b').hover(
            function(){
                $(this).addClass('btn-green-bh');
            },
            function(){
                $(this).removeClass('btn-green-bh');
            }
        );
        $('.btn-green-m').hover(
            function(){
                $(this).addClass('btn-green-mh');
            },
            function(){
                $(this).removeClass('btn-green-mh');
            }
        );
        $('.btn-blue-b').hover(
            function(){
                $(this).addClass('btn-blue-bh');
            },
            function(){
                $(this).removeClass('btn-blue-bh');
            }
        );
        $('.submit-btn').hover(
            function(){
                $(this).addClass('submit-btn-h');
            },
            function(){
                $(this).removeClass('submit-btn-h');
            }
        );
        $('.btn-green-s').hover(
            function(){
                $(this).addClass('btn-green-sh');
            },
            function(){
                $(this).removeClass('btn-green-sh');
            }
        );
    };
    //城市选择
    var _selectCity = function(){
        $('.city .cityselt').hover(
            function(){
                $('.city .cityselt .ct1').addClass('city-on');
                $('.city-list').show();
            },
            function(){
                $('.city .cityselt .ct1').removeClass('city-on');
                $('.city-list').hide();
            }
        );
        $('.city-list a').click(function(){
            $(this).addClass('cur').siblings().removeClass('cur');
            var city = $(this).text();
            $('.city span').text(city);
        });
    };
    //分享
    var _shareTo = function(){
        $(".shareto").click(function(){
            //event.stopPropagation();
            var $share_b = $(this).parent().siblings(".share-b");
            if($share_b.is(":visible")){
                $(this).removeClass("shareto-on");
                $share_b.fadeOut(500);
            }else{
                $share_b.fadeIn(500);
                $(this).addClass("shareto-on");
            }
            return false;
        });
        $('.share-b').bind('mouseover', function(){
            //alert("鼠标移上");
            if(typeof fsave == 'function' ){
                $(document).unbind('click', fsave);
            }
        });
        $('.share-b').bind('mouseout', function(){
            // alert("鼠标移除");
            $(document).bind('click', fsave = function(){
                $('.share-b').hide(600);
                $(".shareto").removeClass('shareto-on');
            });
        });
    };
    //li添加on
    var _liAddon = function(){
        $('.recommend-l .recommend-l-c li').hover(
            function(){
                $(this).addClass('on');
            },
            function(){
                $(this).removeClass('on');
            }
        );
    };
    //过滤信息
    /*var _filter = function(){
        $('.filter p a').click(function(){
            $(this).addClass('on').siblings('a').removeClass('on');
            return false;
        });
    };*/

    function _init(){
        _btnHover();
        _selectCity();
        _shareTo();
        _liAddon();
        //_filter();
    }

    return {
        init: _init
    }
})();

$(function(){
    HWTrip.User.init();
    HWTrip.Common.init();
    HWTrip.Basic.init();
    
    $('.whither ul li[name!="no"]').hover(function(){
        $(this).addClass('curr');
        $(this).children('.morewhither').show();
        var offset = $(this).offset();
    }, function(){
        $(this).removeClass('curr');
        $(this).children('.morewhither').hide();
    });

/*
这里在搜索表单提交后或者根据目的地链接进来， input输入框的值有问题
    $('#searchKey').focus(function(){
        if($(this).val() == '请输入目的地或关键词'){
            $(this).val('');
        }
    });
    $('#searchForm').submit(function(){
        if(window.localStorage){
            localStorage.setItem('searchKey', $('#searchKey').val());
        }
    });
    if(window.localStorage){
        var searchKey = localStorage.getItem('searchKey');
        if(searchKey != '' && searchKey != null){
            $('#searchKey').val(searchKey);
        }
    }*/
    $('.wrap .weixin').live('mouseover',function(){
        var wwpop2 = $('.header .toplinks .wwpop');
        wwpop2.css('display','block');
        $('.toplinks #wwpop2').hover(function(){
            if( wwpop2.css('display') != 'block'){
                wwpop2.css('display','block');
            }
        },function(){
            wwpop2.css('display','none');
        });
        return false;
    });
    
    $('.chaxunma').hover(function(){
        if($('.checkpop').length == 0){
            var _tipHelper = [
                              '<div class="checkpop">',
                              '    <div class="checkpopinnr">',
                              '        <p><strong>产品查询码</strong></p>',
                              '        <p>1:用于通过热线咨询时转接所需</p>',
                              '        <p class="p1">2:产品身份标识</p>',
                              '        <p><strong>操作指南</strong></p>',
                              '        <p>拨打0755-28569999+产品查询码+#</p>',
                              '    </div>',
                              '</div>'
                          ].join('');
            $(this).after(_tipHelper);
        }else{
            $('.checkpop').show();
        }
        var position = $(this).position();
        $('.checkpop').attr('style', 'position:absolute;left:'+position.left+'px;top:'+(position.top+25)+'px;z-index:200;');
    }, function(){
        $('.checkpop').hide();
    });
});
(function($) {
    // @todo Document this.
    $.extend($,{ placeholder: {
        browser_supported: function() {
            return this._supported !== undefined ?
              this._supported :
              ( this._supported = !!('placeholder' in $('<input type="text">')[0]) );
        },
        shim: function(opts) {
            var config = {
                color: '#888',
                cls: 'placeholder',
                selector: 'input[placeholder], textarea[placeholder]'
            };
            $.extend(config,opts);
            return !this.browser_supported() && $(config.selector)._placeholder_shim(config);
        }
    }});

    $.extend($.fn,{
        _placeholder_shim: function(config) {
            function calcPositionCss(target)
            {
                var op = $(target).offsetParent().offset();
                var ot = $(target).offset();
    
                return {
                  top: ot.top - op.top,
                  left: ot.left - op.left,
                  width: $(target).width()
                };
            }
            function adjustToResizing(label) {
                var $target = label.data('target');
                if(typeof $target !== "undefined") {
                    label.css(calcPositionCss($target));
                    $(window).one("resize", function () { adjustToResizing(label); });
                }
            }
            return this.each(function() {
                var $this = $(this);
                if( $this.is(':visible') ) {
                    if( $this.data('placeholder') ) {
                        var $ol = $this.data('placeholder');
                        $ol.css(calcPositionCss($this));
                        return true;
                    }
                    var possible_line_height = {};
                    if( !$this.is('textarea') && $this.css('height') != 'auto') {
                        possible_line_height = { lineHeight: $this.css('height'), whiteSpace: 'nowrap' };
                    }
                    var ol = $('<label />')
                        .text($this.attr('placeholder'))
                        .addClass(config.cls)
                        .css($.extend({
                            position:'absolute',
                            display: 'inline',
                            float:'none',
                            overflow:'hidden',
                            textAlign: 'left',
                            color: config.color,
                            cursor: 'text',
                            paddingTop: $this.css('padding-top'),
                            paddingRight: $this.css('padding-right'),
                            paddingBottom: $this.css('padding-bottom'),
                            paddingLeft: $this.css('padding-left'),
                            fontSize: $this.css('font-size'),
                            fontFamily: $this.css('font-family'),
                            fontStyle: $this.css('font-style'),
                            fontWeight: $this.css('font-weight'),
                            textTransform: $this.css('text-transform'),
                            backgroundColor: 'transparent',
                            zIndex: 99
                        }, possible_line_height))
                        .css(calcPositionCss(this))
                        .attr('for', this.id)
                        .data('target',$this)
                        .click(function(){
                            $(this).data('target').focus();
                        })
                        .insertBefore(this);
                    $this.data('placeholder',ol).focus(function(){
                        ol.hide();
                    }).blur(function() {
                        ol[$this.val().length ? 'hide' : 'show']();
                    }).triggerHandler('blur');
                    $(window).one("resize", function () { adjustToResizing(ol); });
                }
            });
        }
    });
})(jQuery);

jQuery(document).add(window).bind('ready load', function() {
    if (jQuery.placeholder) {
        jQuery.placeholder.shim();
    }
});