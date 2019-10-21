<?php
namespace WindApi\session\driver\options;
/**
 * Interface Options
 * 会话驱动配置接口
 * 驱动配置类都需要继承该接口，该接口提供一个将配置属性转换为数组的方法
 * @package WindApi\session\driver\options
 */
interface Options
{
    /**
     * 将配置转换为属性数组
     * @return array
     */
    public function toArray();
}