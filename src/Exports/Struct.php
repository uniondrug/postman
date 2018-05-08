<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-08
 */
namespace Uniondrug\Postman\Exports;

use Uniondrug\Structs\StructInterface;

/**
 * 导出结构体
 * @package Uniondrug\Postman\Exports
 */
class Struct extends Base
{
    private $reflect;
    private $isBoolean = false;
    private $nests = [];
    private $nextsStructs = [];
    private $properties = [];

    /**
     * Struct constructor.
     * @param string $si
     * @throws \Throwable
     */
    public function __construct(string $si)
    {
        try {
            // 1. is array check
            $regexp = "/\s*\[\s*\]\s*$/";
            preg_match($regexp, $si, $m);
            if (count($m) > 0) {
                $this->isBoolean = true;
                $si = preg_replace($regexp, '', $si);
            }
            // 2. reflection
            $this->reflect = new \ReflectionClass($si);
            $this->parser();
        } catch(\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 遍历属性
     */
    private function parser()
    {
        foreach ($this->reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED) as $property) {
            $p = new Property($property);
            if ($p->isStruct) {
                $this->nests[] = $p->type;
            }
            $this->properties[] = $p;
        }
        foreach ($this->nests as $nestClass) {
            $this->nextsStructs[] = new Struct($nestClass);
        }
    }

    public function markdown()
    {
        $text = $comma = '';
        foreach ($this->properties as $i => $property) {
            $text .= $comma.$property->toMarkdown($i);
        }

        foreach ($this->nextsStructs as $nextsStruct){
            $text .= $nextsStruct->markdown();
        }

        return $text;
    }
}
