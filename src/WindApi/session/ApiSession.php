<?php
namespace WindApi\session;
use WindApi\session\driver\options\Options;
use WindApi\session\models\DefaultSessionModel;

/**
 * WindApi会话工具
 * 提供一系列操作会话的便捷方法
 * Class ApiSession
 * @package WindApi\session
 */
class ApiSession
{

    /**
     * SessionManager 会话驱动类
     * 该变量保存驱动器实例对象
     * @var null
     */
    private static $_sessionManager = null;

    private function __construct()
    {
    }

    /**
     * 设定会话驱动器
     * @param string $type
     * 指定会话驱动类型，默认使用的是'mysql'
     * @param Options $options 配置
     * @param String $model 指定会话数据模型类,模型使用 WindApi\session\models\DefaultSessionModel
     * @return bool
     */
    public static function apply($type = "mysql", Options $options = null, $model = DefaultSessionModel::class){
          self::$_sessionManager = SessionManager::getInstance($type,$options,$model);
          return session_set_save_handler(self::$_sessionManager,true);
    }

    /**
     * 设置会话过期时间
     * @param int $expires_time
     * @return mixed
     */
    public static function setExpiresTime($expires_time = 1800) {
        return self::$_sessionManager->setSessionExpiresTime($expires_time);
    }

    /**
     * 读取会话
     * 该方法先后调用session_id()和session_start()方法
     * 如果调用该方法用于会话之间的切换，请先调用ApiSession::save()方法将当前会话写入并完成再调用此方法
     * @param $session_id
     * 会话ID，请保证会话在当前系统中为唯一
     * @return array $_SESSION 返回当前会话
     */
    public static function load($session_id){
        session_id($session_id);
        @session_start();
        return $_SESSION;
    }

    /**
     * 保存会话并完成
     * 该方法分别调用 session_write_close() ,session_unset() 和 header_remove方法
     * @return bool
     */
    public static function save(){
        @session_commit();
        function_exists("session_abort") ? @session_abort() : @session_write_close(); //兼容旧php版本
        @session_unset();
        @header_remove("Set-Cookie");
        return true;
    }

    /**
     * 清除全部会话数据
     * 该方法调用session_destroy
     * @return bool
     */
    public static function destroy(){
        return session_destroy();
    }

    /**
     * 生成会话ID
     * @return string
     */
    public static function create_id(){
        $char_id = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = "";
        $uuid = substr($char_id, 0, 8).$hyphen
            .substr($char_id, 8, 4).$hyphen
            .substr($char_id,12, 4).$hyphen
            .substr($char_id,16, 4).$hyphen;
        return $uuid;
    }


}