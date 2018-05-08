<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-08
 */
namespace Uniondrug\Postman\Exports;

/**
 * 导出返回结构体
 * @package Uniondrug\Postman\Exports
 */
class Output extends Base
{
    public $markdown = '';
    public $jsonRawBody = '{}';

    /**
     * @param \ReflectionMethod $method
     * @return $this
     */
    public function run(\ReflectionMethod $method)
    {
        $comment = $method->getDocComment();
        if (is_string($comment) && $comment !== '') {
            $this->parser($comment);
        }
        return $this;
    }

    public function toJson()
    {
        return $this->jsonRawBody;
    }

    public function toMarkdown()
    {
        return $this->markdown;
    }

    /**
     * 解析注释
     * @param string $comment
     */
    private function parser(& $comment)
    {
        // 1. not defined
        preg_match("/@output\s+([^\n]+)\n/i", $comment, $m);
        if (count($m) === 0) {
            return;
        }
        // 2. Struct
        try {
            $struct = trim($m[1]);
            $export = new Struct($struct);
            $this->markdown = $export->markdown();
            $this->jsonRawBody = $export->jsonRawBody();
        } catch(\Throwable $e) {
        }
    }
}
