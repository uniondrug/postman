<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-08
 */
namespace Uniondrug\Postman\Structs;

use Uniondrug\Structs\Struct;

class Conf extends Struct
{
    public $name = '';
    public $description = '';
    public $host = '';
    /**
     * @var string
     */
    public $auth;
}
