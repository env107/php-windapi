<?php
namespace WindApi\session\models;


use WindApi\session\BaseSessionModel;

/**
 * Interface ExtraProperties
 * 会话数据属性添加接口
 * 该接口提供一个append方法，如果会话数据模型实现该接口，则要求append方法返回额外添加属性的数组
 * 该返回数组为一个关联数组，键名指名属性名，键值指名属性值
 * @package WindApi\session\models
 */
interface ExtraProperties
{
    /**
     * 添加额外属性
     * @param BaseSessionModel $model
     * @return array
     * 返回二维数组形式，其中键值为属性名，值为属性默认值，可以指定null
     */
    public function append(BaseSessionModel $model);
}