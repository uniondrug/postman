<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-09
 */
namespace Uniondrug\Postman\Parsers;

use Uniondrug\Structs\Struct;

/**
 * 参数解析
 * @package Uniondrug\Postman\Parsers
 */
class Parameters
{
    /**
     * @var bool
     */
    public $isArray = false;
    /**
     * @var Property[]
     */
    public $properties = [];
    /**
     * @var Parameters[]
     */
    public $children = [];
    /**
     * @var Method
     */
    public $method;
    public $reflect;
    private static $inputColumns = [
        'required' => '必须',
        'filterType' => '类型',
        'name' => '字段',
        'min' => '最小值',
        'max' => '最大值',
        'desc' => '描述'
    ];
    private static $outputColumns = [
        'type' => '类型',
        'name' => '字段',
        'desc' => '描述'
    ];

    /**
     * @param string $sn Struct Class Name
     * @param string $ns Namespace
     * @throws \Exception
     */
    public function __construct(Method $method, $sn, $ns)
    {
        $this->method = $method;
        // 1. explain
        $re = "/(\S+)\s*([\[|\]|\s]*)$/";
        preg_match($re, $sn, $m);
        if (count($m) === 0) {
            throw new \Exception("invalid @Input/@Output value");
        }
        // 2. is array
        $this->isArray = trim($m[2]) !== '';
        // 3. class name
        $cn = trim($m[1]);
        if ($cn[0] !== '\\') {
            $cn = '\\'.$ns.'\\'.$cn;
        }
        // 4. reflect
        $reflect = new \ReflectionClass($cn);
        $this->reflect = $reflect;
        foreach ($reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED) as $p) {
            if (!is_a($p->class, Struct::class, true)) {
                continue;
            }
            // 4.1 new Property
            $property = new Property($method, $p);
            if ($property->annotation->isStructType) {
                $this->children[$p->name] = new Parameters($method, $property->annotation->type, $reflect->getNamespaceName());
            }
            // 4.2 children
            $this->properties[$p->name] = $property;
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->properties as $property) {
            if ($property->annotation->isStructType) {
                $p = $this->children[$property->name];
                $data[$property->name] = $p->toArray();
            } else {
                $data[$property->name] = $property->annotation->isArrayType ? [$property->defaultValue()] : $property->defaultValue();
            }
        }
        return $this->isArray ? [$data] : $data;
    }

    /**
     * @return string
     */
    public function toMarkdown($input = false)
    {
        // 1. base level
        $text = '> '.$this->reflect->name.$this->method->eol;
        $text .= $this->thead($input).$this->method->crlf;
        foreach ($this->properties as $property) {
            $text .= $this->tbody($input, $property).$this->method->crlf;
        }
        // 2. nest level
        foreach ($this->children as $child) {
            $text .= $this->method->eol;
            $text .= $child->toMarkdown($input);
        }
        // 3. result
        return $text;
    }

    private function thead($input = false)
    {
        $columns = $input ? self::$inputColumns : self::$outputColumns;
        $separators = [];
        // 1. th
        $text = '|';
        foreach ($columns as $column) {
            $text .= ' '.$column.' |';
            $separators[] = ':--';
        }
        $text .= $this->method->crlf;
        // 2. separator
        $text .= '| '.implode(' | ', $separators).' |';
        // 3. table header
        return $text;
    }

    /**
     * @param boolean  $input
     * @param Property $property
     * @return string
     */
    private function tbody($input, $property)
    {
        $text = '|';
        $columns = $input ? self::$inputColumns : self::$outputColumns;
        foreach ($columns as $tag => $_) {
            $text .= ' '.$this->tbodyTags($property, $tag).' |';
        }
        return $text;
    }

    /**
     * 标签过滤
     * @param Property $property
     * @param string   $tag
     * @return string
     */
    private function tbodyTags($property, $tag)
    {
        $m = 'with'.ucfirst($tag);
        if (method_exists($this, $m)) {
            return $this->{$m}($property);
        }
        return 'x';
    }

    /**
     * @param Property $property
     * @return string
     */
    protected function withRequired($property)
    {
        $required = $property->annotation->validator !== null && $property->annotation->validator->required;
        return $required ? 'YES' : '';
    }

    /**
     * @param Property $property
     * @return string
     */
    protected function withName($property)
    {
        return $property->name;
    }

    /**
     * @param Property $property
     * @return string
     */
    protected function withType($property)
    {
        $type = $property->annotation->type;
        if ($property->annotation->isArrayType) {
            $type .= '[]';
        }
        if ($property->annotation->isStructType) {
            return '`'.$type.'`';
        }
        return $type;
    }

    /**
     * @param Property $property
     * @return string
     */
    protected function withFilterType($property)
    {
        if ($property->annotation->validator !== null && $property->annotation->validator->type !== '') {
            return $property->annotation->validator->type;
        }
        return $this->withType($property);
    }

    /**
     * @param Property $property
     * @return string
     */
    protected function withDesc($property)
    {
        return $property->annotation->typeText;
    }

    /**
     * @param Property $property
     * @return string
     */
    protected function withMax($property)
    {
        return '';
    }

    /**
     * @param Property $property
     * @return string
     */
    protected function withMin($property)
    {
        return '';
    }
}
