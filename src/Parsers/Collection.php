<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-09
 */
namespace Uniondrug\Postman\Parsers;

use App\Errors\Code;
use Phalcon\Di;
use Uniondrug\Postman\Parsers\Abstracts\Base;

/**
 * 解析控制器
 * @package Uniondrug\Postman\Parsers
 */
class Collection extends Base
{
    /**
     * 是否发布文档
     * @var bool
     */
    public $publishDocuments = true;
    /**
     * 名称
     * @var string
     */
    public $name = '';
    /**
     * 描述
     * @var string
     */
    public $description = '';
    /**
     * 域名
     * @var string
     */
    public $host = '';
    /**
     * 是否鉴权
     * @var bool
     */
    public $auth = false;
    /**
     * @var Controller[]
     */
    public $controllers = [];
    public $classMap = [];
    /**
     * @var string
     */
    public $basePath;
    public $codeMap = null;
    private $controllerPath = 'app/Controllers';

    /**
     * Controller constructor.
     * @param string $path 项目路径
     */
    public function __construct(string $path)
    {
        parent::__construct();
        $this->basePath = $path;
        $this->name = $path;
        // 1. load config
        $file = $path.'/postman.json';
        if (file_exists($file)) {
            $text = file_get_contents($file);
            $json = json_decode($text);
            if ($json instanceof \stdClass) {
                isset($json->name) && $this->name = $json->name;
                isset($json->description) && $this->description = $json->description;
                isset($json->host) && $this->host = $json->host;
                isset($json->name) && $this->auth = strtoupper($json->auth) === 'YES';
            }
        }
        // 2. 遍历目录
        $this->scanner($path.'/'.$this->controllerPath);
    }

    /**
     * 解析控制器
     */
    public function parser()
    {
        foreach ($this->classMap as $class) {
            try {
                $controller = new Controller($this, $class);
                $controller->parser();
                $this->controllers[$class] = $controller;
            } catch(\Exception $e) {
            }
        }
    }

    public function getCodeMap()
    {
        if ($this->codeMap === null) {
            $this->codeMap = Code::exportMarkdown();
        }
        return $this->codeMap;
    }

    public function toMarkdown()
    {
    }

    /**
     * 转为POSTMAN
     * 将导出的结果输出到postman.json文件中
     */
    public function toPostman()
    {
        $data = [
            'info' => [
                'name' => $this->name,
                'description' => $this->description,
                "schema" => "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
            ],
            'item' => []
        ];
        foreach ($this->controllers as $controller) {
            $data['item'][] = $controller->toPostman();
        }
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * 扫描Controller目录
     * @param string $path
     */
    private function scanner($path)
    {
        $length = strlen($path);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);
        /**
         * @var \SplFileInfo $info
         */
        foreach ($iterator as $info) {
            // 1. 忽略目录
            if ($info->isDir()) {
                continue;
            }
            // 2. 忽然非Controller文件
            $name = $info->getFilename();
            if (preg_match("/^[_a-zA-Z0-9]+Controller\.php$/", $name) === 0) {
                continue;
            }
            // 3. 读取类名
            $class = '\\App\\Controllers\\'.substr($info->getPathname(), $length + 1, -4);
            $this->classMap[] = $class;
        }
    }
}
