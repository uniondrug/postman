<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Commands;

use Phalcon\Text;
use Uniondrug\Console\Command;
use Uniondrug\Postman\Exports\Info;
use Uniondrug\Postman\Exports\Input;
use Uniondrug\Postman\Exports\Output;
use Uniondrug\Postman\Structs\Conf;
use Uniondrug\Postman\Structs\Results\Collection;

/**
 * 导出POSTMAN格式的API文档
 * @package Uniondrug\Postman\Commands
 */
class Postman extends Command
{
    /**
     * @var Conf
     */
    private $baseConf;
    /**
     * 项目路径
     * @var string
     */
    private $basePath = '';
    /**
     * 控制器路径
     * @var string
     */
    private $controllerPath = '';
    private $ln = "\n";

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $this->basePath = getcwd(); //realpath(__DIR__.'/../../../../../');
        $this->controllerPath = $this->basePath.'/app/Controllers';
        $this->prepare();
        $result = [
            'info' => $this->baseInfo(),
            'item' => $this->baseFolder()
        ];
        // 3. 打印结果
        $collection = Collection::factory($result);
        if ($fp = @fopen('/Users/fuyibing/Desktop/export.json', 'wb+')) {
            fwrite($fp, $collection->toJson());
            fclose($fp);
            echo "[Exported]\n";
            exit;
        }
        print_r($collection->toArray());
    }

    /**
     * 导出前准备
     */
    private function prepare()
    {
        $data = [];
        $confFile = $this->basePath.'/postman.json';
        if (file_exists($confFile)) {
            $confText = file_get_contents($confFile);
            $confData = json_decode($confText, true);
            if (is_array($confData)) {
                $data = $confData;
            }
        }
        $this->baseConf = Conf::factory($data);
    }

    /**
     * 基础/Folder列表
     * @return array
     */
    private function baseFolder()
    {
        $data = [];
        $length = strlen($this->controllerPath);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->controllerPath), \RecursiveIteratorIterator::SELF_FIRST);
        /**
         * @var \SplFileInfo $info
         */
        foreach ($iterator as $info) {
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            if (Text::endsWith($info, 'Controller.php', false)) {
                $className = '\\App\\Controllers'.preg_replace([
                        "/\.([a-zA-Z0-9\.]+)$/",
                        "/\//"
                    ], [
                        "",
                        "\\"
                    ], substr($info->getPathname(), $length));
                try {
                    $data[] = $this->controller($className);
                    break;
                } catch(\Exception $e) {
                    continue;
                }
            }
        }
        return $data;
    }

    /**
     * 基础/Collection信息
     * @return array
     */
    private function baseInfo()
    {
        $description = '> '.$this->baseConf->description;
        $description .= "\\\n\\\n";
        $description .= "部署: ".$this->baseConf->host."\\\n";
        $description .= "更新: ".date('Y-m-d H:i')."\\\n";
        return [
            'name' => $this->baseConf->name,
            'description' => $description
        ];
    }

    /**
     * 解析控制器
     * @param string $className
     * @return array
     * @throws \Throwable
     */
    private function controller($className)
    {
        try {
            $class = new \ReflectionClass($className);
            $info = (new Info())->run($class);
            return [
                'name' => $info->name,
                'description' => $info->description,
                'item' => $this->methods($class, $info->prefix)
            ];
        } catch(\Throwable $e) {
            throw $e;
        }
    }

    /**
     * 解析方法列表
     * @param \ReflectionClass $class
     * @param string           $prefix
     * @return array
     */
    private function methods(\ReflectionClass $class, $prefix)
    {
        $data = [];
        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // 1. not belong controller
            if ($class->name !== $method->class) {
                continue;
            }
            // 2. not action format
            if (preg_match("/^([_a-z0-9]+)Action$/i", $method->name) === 0) {
                continue;
            }
            // 3. method explain
            try {
                $data[] = $this->methodsEach($method, $prefix);
                break;
            } catch(\Throwable $e) {
                continue;
            }
        }
        return $data;
    }

    /**
     * 单个方法解析
     * @param \ReflectionMethod $method
     * @param string            $prefix
     * @return array
     */
    private function methodsEach(\ReflectionMethod $method, $prefix)
    {
        $info = (new Info())->run($method);
        return [
            'name' => $info->name,
            'event' => $this->methodsEvent($method),
            'request' => $this->methodsRequest($method, $info, $prefix),
            'response' => $this->methodsResponse($method)
        ];
    }

    /**
     * 可执行脚本
     * @param \ReflectionMethod $method
     * @return array
     */
    private function methodsEvent(\ReflectionMethod $method)
    {
        return [];
    }

    /**
     * 请求参数
     * @param \ReflectionMethod $method
     * @param Info              $info
     * @param                   $prefix
     * @return array
     */
    private function methodsRequest(\ReflectionMethod $method, Info $info, $prefix)
    {
        // 1. basic
        $description = $info->description;
        // 2. input params
        $input = (new Input())->run($method);
        // 2.1. api
        $description .= '1. `url` '.$prefix.$info->path.$this->ln;
        $description .= '1. `method`  '.$info->method.$this->ln;
        // 2.2. input
        $inputMarkdown = $input->markdown;
        if ($inputMarkdown !== '') {
            $description .= $this->ln.$this->ln."### 入参".$this->ln.$this->ln;
            $description .= $inputMarkdown;
        }
        // 2.3. output params
        $output = (new Output())->run($method);
        $outputMarkdown = $output->markdown;
        if ($outputMarkdown !== '') {
            $description .= $this->ln.$this->ln."### 出参".$this->ln.$this->ln;
            $description .= $outputMarkdown;
        }
        // 4. JSON结构
        $data = [
            'url' => $this->methodsRequestUrl($info, $prefix.$info->path),
            'method' => $info->method,
            'description' => $description
        ];
        if ($this->baseConf->auth) {
            $data['auth'] = $this->mothodsRequestAuth($info);
        }
        if ($info->isPost()) {
            $data['body'] = $this->mothodsRequestBody($input);
        }
        return $data;
    }

    private function mothodsRequestAuth(Info $info)
    {
        return [
            'type' => 'bearer',
            'bearer' => [
                [
                    "key" => "token",
                    "value" => "{{token}}",
                    "type" => "string"
                ]
            ]
        ];
    }

    private function mothodsRequestBody(Input $input)
    {
        return [
            'mode' => 'raw',
            'raw' => $input->toJson()
        ];
    }

    private function methodsRequestUrl(Info $info, $path)
    {
        $host = $this->baseConf->host.'.'.$info->domain;
        $data = [
            'raw' => $info->schema.'://'.$host.$path,
            'protocol' => $info->schema,
            'host' => explode('.', $host),
            'path' => explode('/', substr($path, 1))
        ];
        return $data;
    }

    private function methodsResponse(\ReflectionMethod $method)
    {
        return [];
    }
}
