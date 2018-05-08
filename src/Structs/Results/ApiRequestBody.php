<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * API请求体结构
 * @package Uniondrug\Postman\Structs\Results
 */
class ApiRequestBody extends Struct
{
    public $mode = 'raw';
    public $raw = '';
}
