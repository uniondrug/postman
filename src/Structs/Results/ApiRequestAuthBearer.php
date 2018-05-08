<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * API请求鉴权体结构
 * @package Uniondrug\Postman\Structs\Results
 */
class ApiRequestAuthBearer extends Struct
{
    /**
     * 类型
     * @var string
     */
    public $type = 'string';
    /**
     * 键名
     * @var string
     */
    public $key;
    /**
     * 键值
     * @var string
     */
    public $value;
}
