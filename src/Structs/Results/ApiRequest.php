<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * API请求结构
 * @package Uniondrug\Postman\Structs\Results
 */
class ApiRequest extends Struct
{
    /**
     * 鉴权配置
     * @var ApiRequestAuth
     */
    public $auth;
    /**
     * 请求方式
     * @var string
     */
    public $method = 'GET';
    /**
     * 请求头结构
     * @var ApiRequestHeader
     */
    public $header;
    /**
     * 请求体结构
     * @var ApiRequestBody
     */
    public $body;
    /**
     * 请求URL参数
     * @var ApiRequestUrl
     */
    public $url;
    /**
     * 请求描述
     * @var string
     */
    public $description;
}
