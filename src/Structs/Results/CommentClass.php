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
class CommentClass extends Struct
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
     * 前缀
     */
    public $prefix;
}
