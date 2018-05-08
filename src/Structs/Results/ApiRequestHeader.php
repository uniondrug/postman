<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * API请求头结构
 * @package Uniondrug\Postman\Structs\Results
 */
class ApiRequestHeader extends Struct
{
    public $key;
    public $value;
}
