<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-09
 */
namespace Uniondrug\Postman\Parsers\Abstracts;

use Uniondrug\Postman\Parsers\Annotation;

/**
 * 解析基类
 * @package Uniondrug\Postman\Parsers\Abstracts
 */
abstract class Base
{
    /**
     * @var Annotation
     */
    public $annotation;
    /**
     * 换行符
     * @var string
     */
    public $crlf = "\n";
    public $eol = "\n\n";

    public $schema = '{{protocol}}';
    public $domain = '{{domain}}';
    public $token = '{{token}}';

    public function __construct()
    {
    }
}
