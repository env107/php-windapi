<?php

namespace WindApi;

use RuntimeException as WindApiLoaderException;

/**
 * Class Loader
 * 注册加载器，指定查找类的位置
 * @package WindApi
 */
class Loader
{
    /**
     * Loader的实例对象
     * @var null
     */
    private static $_instance = null;
    /**
     * WindApi库目录路径
     * 以该路径作为WindApi的根目录，以方便加载器的查找
     * @var string
     */
    private $_boot = '';
    /**
     * 类后缀名
     * 指定查找类文件的后缀名
     * @var string
     */
    private $_class_prefix = ".php";

    /**
     * Loader构造方法
     * 储存类库根目录路径以及指定类后缀名
     * Loader constructor.
     * @param string $path
     * 指定类库根目录 例如 /var/www/WindApi
     * 指定目录后，自动加载程序将自动寻找该目录下的类
     * @param string $class_prefix
     * 加载的文件允许使用自定义后缀名，如 .class.php,它告诉Loader将自动加载 .class.php后缀的类文件
     */
    private function __construct($path,$class_prefix)
    {
        $pathinfo = pathinfo(str_replace("\\",DIRECTORY_SEPARATOR,$path));
        $this->_boot = $pathinfo['dirname'].DIRECTORY_SEPARATOR.$pathinfo["basename"];
        if(empty($class_prefix)) {
            $class_prefix = ".php";
        } else if(substr($class_prefix,0,1) != '.') {
            throw new WindApiLoaderException("类'{$class_prefix}'后缀名不合法,必须指定'.'开始");
        }
        $this->_class_prefix = $class_prefix;
    }

    /**
     * 获取全局Loader单例
     * @param string $path 拥有与构造器相同的参数$path
     * @param string $class_prefix 拥有与构造器相同的参数$path
     * @return Loader|null
     */
    private static function getInstance($path,$class_prefix){
        if(self::$_instance == null) {
            return new self($path,$class_prefix);
        }
        return self::$_instance;
    }

    /**
     * 注册自动加载程序
     * @param string $class_prefix
     * 指定加载的类后缀名
     */
    public static function register($class_prefix = '.php'){
        $path = __DIR__;
        $loader = self::getInstance($path,$class_prefix);
        spl_autoload_register([
            $loader,'autoload'
        ]);
    }

    /**
     * 自动加载程序
     * @param string $class 要加载的雷鸣
     */
    protected function autoload($class){
        $pathData = explode("\\",$class);

        if($pathData[0] !== 'WindApi') {
            throw new WindApiLoaderException("类'{$class}'不属于WindApi库");
        }
        //去除库目录命名空间
        array_shift($pathData);
        $path = $this->_boot.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR,$pathData).$this->_class_prefix;
        if(!file_exists($path)) {
            throw new WindApiLoaderException("类文件'{$path}'不存在");
        }
        require_once $path;
    }
}

