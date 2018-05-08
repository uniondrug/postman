<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * API结构
 * @package Uniondrug\Postman\Structs\Results
 */
class Api extends Struct
{
    /**
     * API名称
     * @var string
     */
    public $name;
    /**
     * API事件
     * @var ApiEvent[]
     */
    public $event;
    /**
     * API请求
     * @var ApiRequest
     */
    public $request;
    /**
     * API返回
     * @var ApiResponse[]
     */
    public $response;
}
