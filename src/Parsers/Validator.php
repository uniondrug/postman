<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-09
 */
namespace Uniondrug\Postman\Parsers;

/**
 * 解析属性
 * @package Uniondrug\Postman\Parsers
 */
class Validator
{
    public $type = '';
    public $required = false;
    private static $regexpRequired = "/required[=]*([a-z]*)/i";
    private static $regexpTypes = "/type=[\{]*([_a-z0-9, ]+)[\}]*/i";

    public function __construct($text)
    {
        $this->_required($text);
        $this->_type($text);
    }

    private function _required(& $text)
    {
        preg_match(self::$regexpRequired, $text, $m);
        if (count($m) > 0) {
            $this->required = strtolower(trim($m[1])) !== 'false';
        }
    }

    private function _type(& $text)
    {
        preg_match(self::$regexpTypes, $text, $m);
        if (count($m) > 0) {
            $this->type = $m[1];
        }
    }
}
