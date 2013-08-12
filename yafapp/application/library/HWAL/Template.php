<?php
// vim: set expandtab tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | HWSL PHP Framework v1.0                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2011 HUAWEI Inc. All Rights Reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: yeqiaohui<yeqiaohui@huawei.com>                             |
// +----------------------------------------------------------------------+

/**
 * 模板类
 * 兼容smarty常用语法
 * @category   HWSL
 * @usage
 *
 *  PHP Code :
 *  <?php
 *      $tpl = new HWSL_Template();
 *      $arr = array(array('id'=>1, 'title'=>'title1'),array('id'=>2, 'title'=>'title2'));
 *      $obj = (object)array('id'=>1, 'title'=>'title1');
 *
 *      $tpl->assign('var', 1);
 *      $tpl->assign('arr', $arr);
 *      $tpl->assign('obj', $obj);
 *
 *      $tpl->display('index.tpl');
 *  ?>
 *
 *  Template Code :
 *  <!--{if $var == 1}-->
 *  <div>
 *      <ul>
 *          <!--{foreach $arr as $a}-->
 *          <li><!--{$a['id']}-->：<!--{$a['title']}--></li>
 *          <!--{/foreach}-->
 *      </ul>
 *  </div>
 *  <!--{else}-->
 *  <div>
 *      <span><!--{$obj->id}-->：<!--{$obj->title}--></span>
 *  </div>
 *  <!--{/if}-->
 *
 */
class HWAL_Template {
    /**
     * 模版编译缓存目录
     */
    public $compile_dir = '/data/tmp/compile/';

    /**
     * 模版变量
     */
    private $_vars;

    /**
     * 待替换section
     */
    private $_sect = array();


    /**
     * 构造函数
     */
    public function __construct() {
        $this->_vars = new stdClass;
    }

    /**
     * 设置变量
     *
     * @param   mixed   $key    变量名或者键值对数组/对象
     * @param   mixed   $value  变量值
     * @return  void
     */
    public function assign($key, $value = '') {
        if(is_array($key)) {
            foreach($key as $k=>$v) {
                $this->assign($k, $v);
            }
        } elseif(is_object($key)) {
            foreach(get_object_vars($key) as $k=>$v) {
                $this->assign($k, $v);
            }
        } else {
            $this->_vars->$key = $value;
        }
    }

    /**
     * 设置变量（assign()的别名）
     *
     * @param   mixed   $key    变量名或者键值对数组/对象
     * @param   mixed   $value  变量值
     * @return  void
     */
    public function set($key, $value = '') {
        $this->assign($key, $value);
    }

    /**
     * 显示执行结果
     *
     * @param   string   $template  模版文件名(相对于使用模版的php文件的路径或者绝对路径)
     * @param   array    $vars      附加的模版变量
     * @return  void
     */
    public function display($template, $vars=null) {
        if(!headers_sent() && $vars===null) {
            header('Cache-Control: no-cache');
        }
        if($vars) {
            $this->assign($vars);
        }
        $this->_compile($template);
        include($this->_compiledName($template));
    }

    /**
     * 获取执行结果
     *
     * @param   string   $template  模版文件名(相对于使用模版的php文件的路径或者绝对路径)
     * @return  string              执行结果
     */
    public function fetch($template) {
        $this->_compile($template);
        ob_start();
        include($this->_compiledName($template));
        return ob_get_clean();
    }

    /**
     * 获取编译后的模版路径
     *
     * @param   string   $template  模版文件名(相对于使用模版的php文件的路径或者绝对路径)
     * @return  void
     */
    private function _compiledName($template) {
        return $this->compile_dir . md5(realpath($template)) . '.' . basename($template) . '.php';
    }

    /**
     * 编译模版
     *
     * @param   string   $template  模版文件名(相对于使用模版的php文件的路径或者绝对路径)
     * @return  void
     */
    private function _compile($template) {
        if(!file_exists($this->compile_dir)) {
            mkdir($this->compile_dir, 0777, true);
        }
        $compiled = $this->_compiledName($template);
        $template = realpath($template);
        if($template === false){
            return;
        }
        if(file_exists($compiled) && filemtime($compiled) >= filemtime($template)){
            return;
        }
        $lines = file($template);
        foreach($lines as &$line)  {
            if(preg_match_all('/<!--\{([^{}]+)\}-->/', $line, $matches, PREG_SET_ORDER)) {
                foreach($matches as $match) {
                    $line = str_replace($match[0], $this->_syntax($match[1]), $line);
                }
            }
        }
        file_put_contents($compiled, implode('', $lines));
    }

    /**
     * 模版词法转换
     *
     * @param   string   $string  待转换的字符串
     * @return  string            转换后的字符串
     */
    private function _replace($str) {
        //操作符映射
        $map = array(
                     'neq' => '!=',
                     'ne'  => '!=',
                     'eq'  => '==',
                     'gt'  => '>',
                     'lt'  => '<',
                     'gte' => '>=',
                     'ge'  => '>=',
                     'lte' => '<=',
                     'le'  => '<=',
                     'not' => '!',
                     'mod' => '%',
                    );

        $arr = token_get_all('<?php '.$str.' ?>');
        $ret = '';
        $len = count($arr);
        for($i=0; $i<$len; $i++) {
            if(is_array($arr[$i])) {
                switch($arr[$i][0]) {
                    case T_VARIABLE:
                        if($arr[$i][1] === '$smarty' && $arr[$i+2][1] === 'const') {
                            $arr[$i+1] = $arr[$i+2] = $arr[$i+3] = '';
                        } else {
                            $ret .= '$this->_vars->'.substr($arr[$i][1], 1);
                        }
                        break;
                    case T_LOGICAL_AND:
                        $ret .= '&&';
                        break;
                    case T_LOGICAL_OR:
                        $ret .= '||';
                        break;
                    case T_WHITESPACE:
                        $ret .= ' ';
                        break;
                    case T_CONST:
                        $arr[$i+1] = '';
                        break;
                    case T_COMMENT:
                    case T_OPEN_TAG:
                    case T_CLOSE_TAG:
                        break;
                    case T_STRING:
                        if(is_array($arr[$i-1]) && $arr[$i-1][0]===T_WHITESPACE && is_array($arr[$i+1]) &&  $arr[$i+1][0]===T_WHITESPACE && isset($map[$arr[$i][1]])) {
                            $ret .= $map[$arr[$i][1]];
                        } elseif($this->_sect && $arr[$i-1]==='[' && $arr[$i+1]===']' && in_array($arr[$i][1], $this->_sect)) {
                            $ret .= '$'.$arr[$i][1];
                        } else {
                            $ret .= $arr[$i][1];
                        }
                        break;
                    default:
                        $ret .= $arr[$i][1];
                }
            } elseif($arr[$i] === '.' && is_array($arr[$i+1])) {
                $arr[$i+1][1] = '[\''.$arr[$i+1][1].'\']';
            } else {
                $ret .= $arr[$i];
            }
        }
        return $ret;
    }

    /**
     * 解析键值对
     *
     * @param   string   $string  待解析的字符串，如"a='b' c=$d"
     * @return  array             解析后的数组，如array('a'=>"b", 'c'=>"$d")
     */
    private static function _parseVars($string) {
        $ret = array();
        if(preg_match_all('/([A-Za-z_][\w]*)\s*=\s*(\S+)/', $string, $match, PREG_SET_ORDER)) {
            foreach($match as $m) {
                $ret[$m[1]] = trim($m[2], '\'"');
            }
        }
        return $ret;
    }

    /**
     * 模版句法转换
     *
     * @param   string   $input   待转换的字符串
     * @return  string            转换后的字符串
     */
    private function _syntax($input) {
        if($input{0} == '*' && $input{strlen($input)-1} == '*') {
            return '<!--' . trim($input, '*') . '-->';
        }
        $parts = explode(' ', trim($input), 2);
        $string = '<?php ';
        switch($parts[0]) {
            case 'if':
                $string .= 'if(' . $this->_replace($parts[1]) . ') { ';
                break;
            case 'switch':
                $string .= 'switch(' . $this->_replace($parts[1]) . ') { default:';
                break;
            case 'foreach':
                if(strpos($parts[1], ' as ') === false) {
                    $vars = self::_parseVars($parts[1]);
                    extract($vars);
                    $parts[1] = $from.' as '.(isset($key) ? "\${$key} => \$" : '$').$item;
                }
                $string .= 'foreach(' . $this->_replace($parts[1]) . ') { ';
                break;
            case 'for':
                if(preg_match('/\$(\w+)\s*=\s*(\S+)\s+to\s+(\S+)/', $parts[1], $match)) {
                    $parts[1] = "\${$match[1]}=$match[2]; \${$match[1]}<$match[3]; \${$match[1]}++";
                }
                $string .= 'for(' . $this->_replace($parts[1]) . ') { ';
                break;
            case 'section':
                $vars = self::_parseVars($parts[1]);
                extract($vars);
                $start = isset($start) ? $start : 0;
                $step = isset($step) ? $step : 1;
                $max = isset($max) ? $max : 'count('.$this->_replace($loop).')-1';
                $this->_sect[] = $name;
                $string .= "for(\$$name=$start;\$$name<=$max;\$$name+=$step) { ";
                break;
            case '/if':
            case '/foreach':
            case '/for':
            case '/switch':
                $string .= '}';
                break;
            case '/section':
                $string .= '}';
                array_pop($this->_sect);
                break;
            case 'else':
                if(isset($parts[1]) && strpos(trim($parts[1]), 'if') === 0) { //else if -> elseif
                    $parts[1] = substr(trim($parts[1]), 2);
                    $string .= '} elseif(' . $this->_replace($parts[1]) . ') { ';
                } else {
                    $string .= '} else {';
                }
                break;
            case 'elseif':
                $string .= '} elseif(' . $this->_replace($parts[1]) . ') { ';
                break;
            case 'case':
                $string .= 'break; case ' . $this->_replace($parts[1]) . ':';
                break;
            case 'set':
                $string .= $this->_replace($parts[1]) . ';';
                break;
            case 'include':
                $vars = self::_parseVars($parts[1]);
                $file = $vars['file'];
                unset($vars['file']);
                $vars = var_export($vars, true);
                $string .= '$this->display(\''. APPLICATION_PATH . '/tpl/' . $file."', $vars);";
                break;
            default:
                $string .= 'echo ' . $this->_replace($input) . ';';
                break;
        }
        $string .= ' ?>';
        return $string;
    }
}
?>