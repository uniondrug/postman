<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-08
 */
namespace Uniondrug\Postman\Exports;

/**
 * 导出信息
 * @package Uniondrug\Postman\Exports
 */
class Info extends Base
{
    public $name = '';
    public $method = 'GET';
    public $prefix = '';
    public $path = '';
    public $description = '';
    public $tags = [
        'version' => '',
        'since' => '',
        'autor' => '',
        'date' => ''
    ];

    public function isPost(){
        return in_array($this->method, [
            'DELETE', 'PATCH', 'POST', 'PUT'
        ]);
    }

    /**
     * @param \ReflectionClass|\ReflectionMethod $class
     * @return $this
     */
    public function run($class)
    {
        if (is_object($class) && method_exists($class, 'getDocComment')) {
            $comment = $class->getDocComment();
            if (is_string($comment) && $comment != '') {
                $this->parserInfo($comment);
                $this->parserRoute($comment);
                foreach (array_keys($this->tags) as $tag) {
                    $this->parserTags($comment, $tag);
                }
            }
        }

        $this->name === '' && $this->name = $class->getShortName();
        $this->description === '' && $this->description = '> '.$class->getName();
        $this->description .= $this->ln.$this->ln;

        foreach ($this->tags as $tag => $value) {
            if ($value !== '') {
                $this->description .= '* _'.$tag.'_ : `'.htmlspecialchars($value).'`'.$this->ln;
            }
        }
        return $this;
    }

    /**
     * 解析注释
     * @param string $comment
     */
    private function parserInfo(& $comment)
    {
        // 1. not found
        preg_match("/([^@]+)/", $comment, $m);
        if (count($m) === 0) {
            return;
        }
        // 2. formatter
        $s = preg_replace([
            "/\/[\*]+/",
            "/\n\s*[\*]+/",
            "/\n\*[ ]?/"
        ], [
            " *",
            "\n*",
            "\n"
        ], "\n\n{$m[1]}\n\n");
        $i = 0;
        foreach (explode("\n", $s) as $line) {
            $line = rtrim($line);
            if ($line === '') {
                continue;
            }
            $i++;
            if ($i === 1) {
                $this->name = $line;
            } else {
                if ($i == 2) {
                    $this->description .= '> ';
                } else {
                    $this->description .= $this->ln;
                }
                $this->description .= $line;
            }
        }
    }

    /**
     * @param $comment
     */
    private function parserRoute(& $comment)
    {
        // 2. path & method
        preg_match("/@(Route|RoutePrefix|Get|Post|Patch|Put|Delete|Options|Head)\s*\(([^)]*)\)/i", $comment, $m);
        if (count($m) > 0) {
            // 1.1 method
            $m[1] = strtoupper($m[1]);
            if ($m[1] !== 'ROUTE' && $m[1] !== 'ROUTEPREFIX') {
                $this->method = $m[1];
            }
            // 1.2 path
            $m[2] = trim(preg_replace(["/'|\"/", "/[\/]+$/"], ['', ''], $m[2]));
            if ($m[2] !== '') {
                if ($m[2][0] !== '/') {
                    $m[2] = '/'.$m[2];
                }
                if ($m[1] === 'ROUTEPREFIX'){
                    $this->prefix = $m[2];
                } else {
                    $this->path = $m[2];
                }
            }
        }
    }

    /**
     * 解析标签
     * @param string $comment
     * @param string $tag
     */
    private function parserTags(& $comment, $tag)
    {
        if (!isset($this->tags[$tag])) {
            return;
        }
        if (preg_match("/@{$tag}\s+([^\n]*)\n/i", $comment, $m)) {
            $m[1] = trim($m[1]);
            if ($m[1] !== '') {
                $this->tags[$tag] = $m[1];
            }
        }
    }
}
