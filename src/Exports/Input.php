<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-08
 */
namespace Uniondrug\Postman\Exports;

/**
 * 导出入参结构体
 * @package Uniondrug\Postman\Exports
 */
class Input extends Base
{
    public $path = '/';
    public $method = 'GET';
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
            // 1. route
            preg_match("/@(Route|Get|Post|Patch|Put|Delete|Options|Head)\s*\(([\)]*)\)/i", $comment, $m);
            if (count($m) > 0) {
                // 1.1 method
                $m[1] = strtoupper($m[1]);
                if ($m[1] !== 'ROUTE') {
                    $this->method = $m[1];
                }
                // 1.2 path
                $m[2] = trim(preg_replace([
                    "/'|\"/",
                    "/[\/]+$/"
                ], [
                    '',
                    ''
                ], $m[1]));
                if ($m[2] !== '') {
                    if ($m[2][0] !== '/') {
                        $m[2] = '/'.$m[2];
                    }
                    $this->path = $m[2];
                }
            }
            // 2. input
            $this->parser($comment);
        }
        return $this;
    }

    public function toJson()
    {
        return $this->jsonRawBody;
    }

    public function toMarkdown(){
        return $this->markdown;
    }

    /**
     * 解析注释
     * @param string $comment
     */
    private function parser(& $comment)
    {
        // 1. not defined
        preg_match("/@input\s+([^\n]+)\n/i", $comment, $m);
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
