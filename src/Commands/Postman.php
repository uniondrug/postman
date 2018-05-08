<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Commands;

use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Text;
use Uniondrug\Console\Command;
use Uniondrug\Framework\Container;
use Uniondrug\Postman\Structs\Results\Collection;
use Uniondrug\Postman\Structs\Results\CommentClass;
use Uniondrug\Postman\Structs\Results\CommentMethod;

/**
 * 导出POSTMAN格式的API文档
 * @package Uniondrug\Postman\Commands
 */
class Postman extends Command
{
    private $appName;
    private $appHostPrefix;
    private $appHostProtocol = '{{protocol}}';
    private $appHostDomain = '{{domain}}';
    private $appAuthentication = false;
    private $basePath = '';
    private $controllerPath;
    /**
     * @var \ReflectionClass
     */
    private $reflect;
    /**
     * @var CommentClass
     */
    private $reflectComment;
    /**
     * @var \ReflectionMethod
     */
    private $method;
    /**
     * @var CommentMethod
     */
    private $methodComment;

    /**
     * @inheritdoc
     */
    public function handle()
    {
        /**
         * @var Container $di
         * @var Config    $conf
         */
        $di = Di::getDefault();
        $conf = $di->getConfig()->path('app');
        $middleware = $di->getConfig()->path('middleware');
        if ($middleware instanceof Config) {
            if (isset($middleware->token)) {
                $this->appAuthentication = true;
            }
        }
        if (!isset($conf->appName) || !isset($conf->appHostPrefix)) {
            throw new \Exception("app config variables `appName`、`appHostPrefix` not defined.");
        }
        $this->appName = $conf->appName;
        $this->appHostPrefix = $conf->appHostPrefix;
        // 1. 计算物理路径
        $this->basePath = realpath(__DIR__.'/../../../../../app');
        $this->controllerPath = $this->basePath.'/Controllers';
        // 2. 创建结果实例
        $collection = Collection::factory([
            'info' => $this->collectionInfo(),
            'item' => $this->collectionItem()
        ]);
        // 3. 打印结果
        if ($fp = @fopen('/Users/fuyibing/Desktop/export.json', 'wb+')) {
            fwrite($fp, $collection->toJson());
            fclose($fp);
            echo "[Exported]\n";
            exit;
        }
        print_r($collection->toArray());
    }

    /**
     * 基础信息
     * @return array
     */
    public function collectionInfo()
    {
        /**
         * @var Container $di
         */
        return [
            'name' => $this->appName
        ];
    }

    /**
     * 扫描控制器目录
     */
    private function collectionItem()
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
                    $data[] = $this->folder($className);
                    break;
                } catch(\Exception $e) {
                }
            }
        }
        return $data;
    }

    /**
     * 类反射
     * @param string $className
     * @return array
     * @throws \Exception
     */
    private function folder(string $className)
    {
        // 1. 类反射
        try {
            $this->reflect = new \ReflectionClass($className);
            $this->initClassComments($this->reflect);
        } catch(\Exception $e) {
            throw $e;
        }
        // 2. 类注释
        return [
            'name' => $this->reflectComment->name ?: $this->reflect->getShortName(),
            'description' => $this->reflectComment->description."\n\n1. ".$this->reflect->name,
            'item' => $this->items($this->reflect->name)
        ];
    }

    /**
     * API列表记录
     * @return array
     */
    private function items($className)
    {
        $items = [];
        foreach ($this->reflect->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($className !== $method->class || preg_match("/Action$/", $method->name) === 0) {
                continue;
            }
            try {
                $items[] = $this->itemApi($method);
            } catch(\Exception $e) {
            }
        }
        return $items;
    }

    /**
     * API接口
     * @param \ReflectionMethod $method
     * @return array
     */
    private function itemApi(\ReflectionMethod $method)
    {
        $this->initMethodComments($method);
        return [
            'name' => $this->methodComment->name,
            'event' => $this->itemEvent($method),
            'request' => $this->itemRequest(),
            'response' => $this->itemResponse()
        ];
    }

    /**
     * 自动化测试
     * @return array
     */
    private function itemEvent(\ReflectionMethod $method)
    {
        return [
            [
                "listen" => "test",
                "script" => [
                    "id" => md5($method->name),
                    "type" => "text/javascript",
                    "exec" => [
                        "pm.test(\"".$this->methodComment->name."\", function(){",
                        "    pm.response.to.be.ok;",
                        "    json = pm.response.json();",
                        "    pm.expect(\"0\").to.equal(json.errno);",
                        "    duration = parseInt(pm.environment.get(\"duration\"));",
                        "    pm.expect(pm.response.responseTime).to.be.below(duration)",
                        "});"
                    ]
                ]
            ]
        ];
    }

    /**
     * API请求结构
     * @return array
     */
    private function itemRequest()
    {
        $data = [
            'method' => $this->methodComment->method,
            'url' => $this->itemRequestUrl(),
            'description' => $this->methodComment->description
        ];
        if ($this->methodComment->isPost()) {
            $data['body'] = $this->itemRequesetBody();
        }
        if ($this->appAuthentication) {
            $data['auth'] = $this->itemRequestAuth();
        }
        return $data;
    }

    /**
     * Token验证
     * @return array
     */
    private function itemRequestAuth()
    {
        return [
            "type" => "bearer",
            "bearer" => [
                [
                    "type" => "string",
                    "key" => "token",
                    "value" => "{{token}}"
                ]
            ]
        ];
    }

    /**
     * 请求体结构
     * @return array
     */
    private function itemRequesetBody()
    {
        return [
            "mode" => "raw",
            "raw" => "{}"
        ];
    }

    /**
     * URL结构
     * @return array
     */
    private function itemRequestUrl()
    {
        return [
            "raw" => $this->appHostProtocol."://".$this->appHostPrefix.".".$this->appHostDomain.$this->methodComment->path,
            "protocol" => $this->appHostProtocol,
            "host" => explode(".", $this->appHostPrefix.".".$this->appHostDomain),
            "path" => explode("/", preg_replace("/(^[\/]+)/", "", $this->methodComment->path))
        ];
    }

    private function itemResponse()
    {
        return [];
    }

    /**
     * 初始化类注释
     */
    private function initClassComments(\ReflectionClass $reflect)
    {
        // 1: comment initialize
        $comment = $reflect->getDocComment();
        // 1.1: get info from comment
        $info = $this->getInfoFromComment($comment);
        // 2: struct initialize
        $struct = CommentClass::factory($info);
        // 2.1: name
        if ($struct->name === "") {
            $struct->name = $reflect->getShortName();
        }
        // 2.2: prefix
        $struct->prefix = $this->getPrefixFromComment($comment);
        // n. cached
        $this->reflectComment = $struct;
    }

    /**
     * 初始化方法注释
     */
    private function initMethodComments(\ReflectionMethod $method)
    {
        // 1: comment initialize
        $comment = $method->getDocComment();
        // 1.1: get info from comment
        $info = $this->getInfoFromComment($comment);
        $info = array_merge($info, $this->getPathFromComment($method, $comment));
        // 2: struct initialize
        $struct = CommentMethod::factory($info);
        // 2.1: name
        if ($struct->name === "") {
            $struct->name = $method->getShortName();
        }
        // 2.2: path
        $struct->path = $this->reflectComment->prefix.$struct->path;
        // n. cached
        $this->methodComment = $struct;
    }

    /**
     * 从注释中提取名称和注释
     * @param string $comment
     * @return array
     */
    private function getInfoFromComment(& $comment)
    {
        $data = [
            "name" => "",
            "description" => ""
        ];
        // 1. 空注释
        if (!is_string($comment) || $comment === "") {
            return $data;
        }
        // 2. 无描述文字
        if (preg_match("/([^@]+)/", $comment, $m) === 0) {
            return $data;
        }
        // 3. 处理注释
        $m[1] = preg_replace([
            "/\/[\*]+/",
            "/\n\s*[\*]+\s*/"
        ], [
            "*",
            "\n*"
        ], $m[1]);
        // 4. 获取正文
        preg_match_all("/[\*]+([^\*]+)\n/", $m[1], $x);
        $index = 0;
        $buffer = [];
        foreach ($x[1] as $s) {
            $s = trim($s);
            if ($s === "") {
                continue;
            }
            $index++;
            if ($index === 1) {
                $data["name"] = $s;
            } else {
                $buffer[] = $s;
            }
        }
        $data["description"] = implode("\\n", $buffer);
        return $data;
    }

    /**
     * 从注释中读取请求路径
     * @param \ReflectionMethod $method
     * @param string            $comment
     * @return []
     */
    private function getPathFromComment(\ReflectionMethod $method, & $comment)
    {
        $data = [
            "method" => "GET",
            "path" => ""
        ];
        if (preg_match("/@(ROUTE|GET|POST|DELETE|PUT|PATCH|HEAD|OPTIONS)\(([^\)]*)\)/i", $comment, $m) > 0) {
            // 1. method
            $m[1] = strtoupper($m[1]);
            if ($m[1] !== "ROUTE") {
                $data["method"] = $m[1];
            }
            // 2. path
            $m[2] = preg_replace("/'|\"/", '', $m[2]);
            $m[2] = trim($m[2]);
            if ($m[2] !== '') {
                if ($m[2][0] !== '/') {
                    $m[2] = '/'.$m[2];
                }
                $data['path'] = $m[2];
            }
        }
        // 3. create path from method name
        if ($data['path'] === '') {
            $data['path'] = '/'.preg_replace("/Action$/", '', $method->getShortName());
        }
        return $data;
    }

    /**
     * 读取请求路径前缀
     * @param string $comment
     * @return string
     */
    private function getPrefixFromComment(& $comment)
    {
        $prefix = '';
        if (preg_match("/@RoutePrefix\(([^\)]*)\)/i", $comment, $m) > 0) {
            $m[1] = preg_replace("/'|\"/", '', $m[1]);
            $m[1] = trim($m[1]);
            if ($m[1] !== '') {
                if ($m[1][0] !== '/') {
                    $m[1] = '/'.$m[1];
                }
                $prefix = $m[1];
            }
        }
        return $prefix;
    }

    /**
     * 从评论中提取测试脚本
     * @param string $comment
     */
    private function getScriptFromComment(& $comment)
    {
    }
}
