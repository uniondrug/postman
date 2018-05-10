<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-09
 */
namespace Uniondrug\Postman\Parsers;

use Uniondrug\Postman\Parsers\Abstracts\Base;

/**
 * 解析方法
 * @package Uniondrug\Postman\Parsers
 */
class Method extends Base
{
    /**
     * @var Collection
     */
    public $collection;
    /**
     * @var Controller
     */
    public $controller;
    /**
     * @var \ReflectionMethod
     */
    public $reflect;
    /**
     * @var Parameters
     */
    private $inputParameter = null;
    /**
     * @var Parameters
     */
    private $outputParameter = null;

    /**
     * Method constructor.
     * @param Collection        $collection
     * @param Controller        $controller
     * @param \ReflectionMethod $reflect
     */
    public function __construct(Collection $collection, Controller $controller, \ReflectionMethod $reflect)
    {
        parent::__construct();
        $this->collection = $collection;
        $this->controller = $controller;
        $this->reflect = $reflect;
    }

    /**
     * 执行解析
     */
    public function parser()
    {
        // 1. 注解
        $this->annotation = new Annotation($this->reflect);
        $this->annotation->info();
        $this->annotation->requeset();
        $this->annotation->input();
        $this->annotation->output();
        $this->annotation->test();
        // 1. 补齐
        if ($this->annotation->name === '') {
            $this->annotation->name = $this->reflect->name.'()';
        }
        // 2. 入参
        if ($this->annotation->input !== '') {
            try {
                $this->inputParameter = new Parameters($this, $this->annotation->input, $this->controller->reflect->getNamespaceName());
            } catch(\Exception $e) {
            }
        }
        // 3. 出参
        if ($this->annotation->output !== '') {
            try {
                $this->outputParameter = new Parameters($this, $this->annotation->output, $this->controller->reflect->getNamespaceName());
            } catch(\Exception $e) {
            }
        }
    }

    /**
     *
     */
    public function toMarkdown()
    {
    }

    /**
     * @return array
     */
    public function toPostman()
    {
        $data = [
            'name' => $this->annotation->name,
            'event' => $this->toPostmanEvent(),
            'request' => $this->toPostmanRequest(),
            'response' => $this->toPostmanResponse()
        ];
        return $data;
    }

    public function toPostmanEvent()
    {
        $exec = [];
        $exec[] = 'pm.test("'.$this->annotation->name.'", function(){';
        $exec[] = '    var json = pm.response.json();';
        $exec[] = '    pm.response.to.be.ok;';
        $exec[] = '    pm.expect("0").to.equal(json.errno);';
        $exec[] = '});';
        return [
            [
                'listen' => 'test',
                'script' => [
                    'id' => md5($this->controller->reflect->name.'::'.$this->reflect->name),
                    'type' => 'text/javascript',
                    'exec' => $exec
                ]
            ]
        ];
    }

    public function toPostmanRequest()
    {
        $data = ['method' => $this->annotation->method];
        // auth
        if ($this->collection->auth) {
            $data['auth'] = [
                'type' => 'bearer',
                'bearer' => [
                    [
                        'key' => 'token',
                        'value' => $this->token,
                        'type' => 'string'
                    ]
                ]
            ];
        }
        // url
        $data['url'] = [
            'raw' => $this->schema.'://'.$this->collection->host.'.'.$this->domain.$this->annotation->path,
            'protocol' => $this->schema,
            'host' => explode('.', $this->collection->host.'.'.$this->domain),
            'path' => explode('/', substr($this->annotation->path, 1))
        ];
        // post body
        if ($this->annotation->isPostMethod) {
            $body = '{}';
            if ($this->inputParameter !== null) {
                $body = json_encode($this->inputParameter->toArray(), JSON_PRETTY_PRINT);
            }
            $data['body'] = [
                'mode' => 'raw',
                'raw' => $body
            ];
        }
        // desc
        $data['description'] = $this->headerText().$this->eol.$this->inputText().$this->eol.$this->outputText().$this->eol.$this->outputCode();
        $data['description'] .= $this->eol;
        $data['description'] .= '### 编码';
        $data['description'] .= $this->eol;
        $data['description'] .= $this->collection->getCodeMap();
        return $data;
    }

    public function toPostmanResponse()
    {
        return [];
    }

    private function afterParser()
    {
        $text = $this->headerText();
        $text .= $this->eol.$this->inputText();
        $text .= $this->eol.$this->inputCode();
        $text .= $this->eol.$this->outputText();
        $text .= $this->eol.$this->outputCode();
        if (false !== ($fp = @fopen($this->collection->basePath.'/vendor/e.md', 'wb+'))) {
            fwrite($fp, $text);
            fclose($fp);
        }
        echo "---------\n";
        echo $text;
        echo "\n---------------\n";
        exit;
    }

    /**
     * 文档头信息
     * @return string
     */
    private function headerText()
    {
        // 1. title
        $text = ''; //'# '.$this->annotation->name;
        // 2. description
        if ($this->annotation->description !== '') {
            //$text .= $this->eol.'> '.$this->annotation->description;
            $text .= '> '.$this->annotation->description;
        }
        // 3. tags
        $text .= $this->eol;
        $text .= '* **接口** : `'.$this->annotation->method.' '.$this->controller->annotation->prefix.$this->annotation->path.'`'.$this->crlf;
        if ($this->annotation->input !== '') {
            $text .= '* **入参** : `'.$this->annotation->input.'`'.$this->crlf;
        }
        if ($this->annotation->output !== '') {
            $text .= '* **出参** : `'.$this->annotation->output.'`'.$this->crlf;
        }
        $text .= '* **文件** : `'.$this->controller->filename.'`'.$this->crlf;
        $text .= '* **执行** : `'.$this->controller->reflect->name.'::'.$this->reflect->name.'()'.'`'.$this->crlf;
        $text .= '* **导出** : `'.date('Y-m-d H:i').'`';
        // 4. 返回
        return $text;
    }

    /**
     * 入参Text文档
     */
    private function inputText()
    {
        $text = '';
        // 1. not defined
        if ($this->annotation->input === '') {
            return $text;
        }
        // 2. 格式化
        $text .= '### 入参'.$this->eol;
        // 3. 结构体错误
        if ($this->inputParameter === null) {
            $text .= '> 结构体 `'.$this->annotation->input.'` 不能正确解析';
            return $text;
        }
        // 4. 导出文档
        $text .= $this->inputParameter->toMarkdown(true);
        // 5. 返回结果
        return $text;
    }

    /**
     * 入参Code片段
     * @return string
     */
    private function inputCode()
    {
        $text = '';
        // 1. not defined
        if ($this->inputParameter === null) {
            return $text;
        }
        // 2. json string
        $text .= '*示例*'.$this->eol;
        $text .= '```'.$this->crlf;
        $text .= json_encode($this->inputParameter->toArray(), JSON_PRETTY_PRINT).$this->crlf;
        $text .= '```';
        return $text;
    }

    /**
     * 出参Text文档
     * @return string
     */
    private function outputText()
    {
        $text = '';
        // 1. not defined
        if ($this->annotation->output === '') {
            return $text;
        }
        // 2. 格式化
        $text .= '### 出参'.$this->eol;
        // 3. 结构体错误
        if ($this->outputParameter === null) {
            $text .= '> 结构体 `'.$this->annotation->output.'` 不能正确解析';
            return $text;
        }
        // 4. 导出文档
        $text .= $this->outputParameter->toMarkdown(false);
        // 5. 返回结果
        return $text;
    }

    /**
     * 出参Code片段
     * @return string
     */
    private function outputCode()
    {
        $text = '';
        // 1. not defined
        if ($this->outputParameter === null) {
            return $text;
        }
        // 2. json string
        $text .= '*示例*'.$this->eol;
        $text .= '```'.$this->crlf;
        $text .= json_encode($this->outputParameter->toArray(), JSON_PRETTY_PRINT).$this->crlf;
        $text .= '```';
        return $text;
    }
}
