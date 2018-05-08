<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * @package Uniondrug\Postman\Structs\Results
 */
class CommentMethod extends Struct
{
    /**
     * 名称
     * @var string
     */
    public $name;
    /**
     * 描述
     * @var string
     */
    public $description;
    /**
     * 请求方式
     * @var string
     */
    public $method;
    /**
     * 请求路径
     * @var string
     */
    public $path;

    /**
     * @return bool
     */
    public function isPost()
    {
        return in_array($this->method, [
            "DELETE",
            "PATCH",
            "POST",
            "PUT"
        ]);
    }
}
