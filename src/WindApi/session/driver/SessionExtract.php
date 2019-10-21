<?php

namespace WindApi\session\driver;


use WindApi\session\BaseSessionModel;
use WindApi\session\driver\options\Options;

/**
 * Interface SessionExtract
 * 会话数据操作接口，包含会话读取，写入，删除等接口
 * 会话驱动必须实现该接口
 * @package WindApi\session\driver
 */
interface SessionExtract
{
    /**
     * 读取会话
     * 指定会话驱动读取会话信息，并返回会话数据模型
     * @param BaseSessionModel $fitModel
     * 会话数据模型，会话驱动读取到会话数据之后，开发者应该调用该数据模型的fit()方法，将数据注入模型中，并返回
     * @return BaseSessionModel | null
     */
    public function loadSession(BaseSessionModel $fitModel);

    /**
     * 新增会话
     * @param BaseSessionModel $model
     * 开发者构造该会话数据模型之后传递到该方法中，随后会话数据驱动会将该模型的数据保存到持久化服务中
     * @return bool
     */
    public function insertSession(BaseSessionModel $model);

    /**
     * 更新会话
     * @param BaseSessionModel $model
     * 开发者构造该会话数据模型之后传递到该方法中，随后会话数据驱动会将该模型的数据保存到持久化服务中
     * @return bool
     */
    public function updateSession(BaseSessionModel $model);

    /**
     * 删除会话
     * @param BaseSessionModel $removeModel 删除的会话模型
     * @return bool
     */
    public function removeSession(BaseSessionModel $removeModel);

    /**
     * 删除指定会话片段
     * 该接口用于SessionManager中的gc()方法
     * @param array $group 指定会话ID的数组
     * @return bool
     */
    public function removeSessionGroup(array $group);

    /**
     * 获取已经失效的会话片段
     * 该接口用于SessionManager中的gc()方法
     * @param integer $timestamp 参照该时间戳之前的会话作为已经失效的片段
     * @return array 返回失效片段的session_id
     */
    public function getLostSession($timestamp);

    /**
     * 关闭会话操作
     * @param BaseSessionModel $sessionModel 会话数据模型
     * @return bool
     */
    public function close(BaseSessionModel $sessionModel);

    /**
     * 开启会话操作
     * @param BaseSessionModel $sessionModel
     * @param string $save_path 保存会话的路径
     * @param string $session_name 保存会话的名称
     * @return bool
     */
    public function open(BaseSessionModel $sessionModel,$save_path,$session_name);

    /**
     * 单对象实例模式
     * @param Options $options
     * @return SessionExtract
     */
    public static function getSessionDriverInstance(Options $options);
}