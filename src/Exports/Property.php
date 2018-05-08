<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-08
 */
namespace Uniondrug\Postman\Exports;

/**
 * 出/入参属性
 * @package Uniondrug\Postman\Exports
 */
class Property extends Base
{
    public $required = false;
    public $type = 'string';
    public $isArray = false;
    public $isStruct = false;
    public $name = '';
    public $value = null;
    public $message = '';
    public $class = '';

    /**
     * Property constructor.
     * @param \ReflectionProperty $p
     */
    public function __construct(\ReflectionProperty $p)
    {
        $this->name = $p->name;
        $this->class = $p->getDeclaringClass()->getName();
        $comment = $p->getDocComment();
        $this->parser($p, $comment);
    }

    /**
     * 转为Markdown格式
     * @param int $i
     * @return string
     */
    public function toMarkdown($i = 0)
    {
        $text = "";
        if ($i === 0) {
            $text .= "### ".$this->class.$this->ln;
            $text .= "| 必须 | 类型 | 字段 | 描述 |".$this->ln;
            $text .= "| -- | -- | -- | :-- |".$this->ln;
        }
        $type = $this->type;
        if ($this->isArray) {
            $type = "{$type}[]";
        }
        if ($this->isStruct) {
            $type = "`{$type}`";
        }
        $text .= "| ".($this->required ? 'YES' : '-')." | {$type} | {$this->name} | {$this->message} |".$this->ln;
        return $text;
    }

    /**
     * 解析注释
     * @param \ReflectionProperty $p
     * @param string              $comment
     */
    private function parser(\ReflectionProperty $p, & $comment)
    {
        // 1. validator
        preg_match("/@validator\(([^\)]*)\)/", $comment, $m);
        if (count($m) > 0) {
            // 1. required
            if (preg_match("/required[=]?([a-z]*)/i", $m[1], $q) > 0) {
                $this->required = ($q[1] !== '' && strtolower($q[1]) !== 'false');
            }
        }
        // 2. type
        preg_match("/@var\s+([_a-z0-9\\\[\]]*)([^\n]*)\n/i", $comment, $t);
        if (count($t) > 0) {
            // 2.1 message
            $t[2] = trim($t[2]);
            if ($t[2] !== '') {
                $this->message = $t[2];
            }
            // 2.2 type define
            $regexp = "/\s*\[\s*\]\s*$/";
            preg_match($regexp, $t[1], $tx);
            if (count($tx) > 0) {
                $this->isArray = true;
                $t[1] = preg_replace($regexp, '', $t[1]);
            }
            // 2.3 struct nest
            $this->type = $t[1];
            preg_match("/^([A-Z])/", $t[1], $ts);
            if (count($ts) > 0) {
                $this->isStruct = true;
                if ($this->type[0] !== '\\') {
                    $this->type = '\\'.$p->getDeclaringClass()->getNamespaceName().'\\'.$this->type;
                }
            }
        }
        // 3. message
        if ($this->message === '') {
            preg_match("/([^@]+)/", $comment, $x);
            if (count($x) > 0) {
                $x = preg_replace([
                    "/\/[\*]+/",
                    "/\n\s*\*/",
                    "/\n\s*\n/"
                ], [
                    "*",
                    "\n",
                    ""
                ], "\n".$x[1]."\n");
                $this->message = trim($x);
            }
        }
    }
}
