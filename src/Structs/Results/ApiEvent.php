<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * API事件结构
 * @package Uniondrug\Postman\Structs\Results
 */
class ApiEvent extends Struct
{
    /**
     * 事件监听
     * @var string
     */
    public $listen = 'test';
    /**
     * 脚本数组
     * @var ApiEventScript
     */
    public $script;
}
