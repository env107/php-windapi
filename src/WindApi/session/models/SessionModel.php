<?php

namespace WindApi\session\models;


use WindApi\session\BaseSessionModel;

/**
 * Interface SessionModel
 * 会话数据模型接口
 * 该接口提供注入和生成模型属性数据的方法
 * @package WindApi\session\models
 */
interface SessionModel
{
    /**
     * 注入到会话数据模型
     * @param array $session
     * @return BaseSessionModel
     */
    public function fit(array $session);

    /**
     * 生成数组数据
     * @return array
     */
    public function toArray();
}