<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * API事件脚本结构
 * @package Uniondrug\Postman\Structs\Results
 */
class ApiEventScript extends Struct
{
    /**
     * 事件监听
     * @var string
     */
//    public $id = '';
    /**
     * 脚本类型
     * @var string
     */
    public $type = 'text/javascript';
    /**
     * 脚本行信息
     * @var string[]
     */
    public $exec;
}
