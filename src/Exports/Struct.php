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
    private $isArray = false;
    private $nests = [];
    private $nextsIndex = [];
    private $nextsStructs = [];
    private $properties = [];
    private $index = 0;

    /**
     * Struct constructor.
     * @param string $si
     * @throws \Throwable
     */
    public function __construct(string $si, $index = 0)
    {
        $this->index = $index;
        try {
            // 1. is array check
            $regexp = "/\s*\[\s*\]\s*$/";
            preg_match($regexp, $si, $m);
            if (count($m) > 0) {
                $this->isArray = true;
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
                $index = $this->index + 1;
                $p->index = $index;
                $this->nests[$p->name] = $p->type;
                $this->nextsIndex[$p->name] = $index;
            }
            $this->properties[] = $p;
        }
        foreach ($this->nests as $name => $nestClass) {
            $this->nextsStructs[$name] = new Struct($nestClass, $this->nextsIndex[$name]);
        }
    }

    public function jsonRawBody()
    {
        $data = $this->toArray();
        return json_encode($data, true);
    }

    public function markdown()
    {
        /**
         * @var Property $p
         */
        $text = $comma = '';
        foreach ($this->properties as $i => $p) {
            $text .= $comma.$p->toMarkdown($i, $this->index);
        }
        foreach ($this->nextsStructs as $nextsStruct) {
            $text .= $nextsStruct->markdown();
        }
        return $text;
    }

    public function toArray()
    {
        /**
         * @var Property $p
         * @var Struct   $s
         */
        $data = [];
        foreach ($this->properties as $p) {
            $name = $p->name;
            if (isset($this->nests[$name])) {
                $s = $this->nextsStructs[$name];
                if ($p->isArray) {
                    $data[$name] = [
                        $s->toArray()
                    ];
                } else {
                    $data[$name] = $s->toArray();
                }
            } else {
                if ($p->isArray) {
                    $data[$name] = [
                        $p->defaultValue()
                    ];
                } else {
                    $data[$name] = $p->defaultValue();
                }
            }
        }
        return $this->isArray ? [$data] : $data;
    }
}
