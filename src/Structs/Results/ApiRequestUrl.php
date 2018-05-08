<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * API请求地址结构
 * @package Uniondrug\Postman\Structs\Results
 */
class ApiRequestUrl extends Struct
{
    /**
     * @var string
     */
    public $raw = '';
    /**
     * @var string
     */
    public $protocol = '';
    /**
     * @var string[]
     */
    public $host;
    /**
     * @var string[]
     */
    public $path;
}
