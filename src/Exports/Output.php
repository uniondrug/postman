<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-08
 */
namespace Uniondrug\Postman\Exports;

/**
 * 导出返回结构体
 * @package Uniondrug\Postman\Exports
 */
class Output extends Base
{
    public $markdown = '';

    /**
     * @param \ReflectionMethod $method
     * @return $this
     */
    public function run(\ReflectionMethod $method)
    {
        return $this;
    }
}
