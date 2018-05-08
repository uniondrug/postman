<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * API请求鉴权结构
 * @package Uniondrug\Postman\Structs\Results
 */
class ApiRequestAuth extends Struct
{
    /**
     * 类型
     * @var string
     */
    public $type = 'bearer';
    /**
     * 结构
     * @var ApiRequestAuthBearer[]
     */
    public $bearer;
}
