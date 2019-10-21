<?php

namespace WindApi\session\models;
use WindApi\session\BaseSessionModel;

/**
 * Class DefaultSessionModel
 * 默认的会话数据模型
 * @package WindApi\session\models
 */
class DefaultSessionModel extends BaseSessionModel implements ExtraProperties
{

    /**
     * 添加额外属性
     * @param BaseSessionModel $model
     * @return array 返回额外属性的数组，以二维数组的方式返回
     */
    public function append(BaseSessionModel $model)
    {
        return [
            'remote_ip' => ''
        ];
    }
}