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
    public $publishTo = 'docs/api';
    public $publishPostmanTo = 'docs';
    /**
     * 名称
     * @var string
     */
    public $name = '';
    public $prefix = '';
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
        // 1. load config
        $file = $path.'/postman.json';
        if (file_exists($file)) {
            $text = file_get_contents($file);
            $json = json_decode($text);
            if ($json instanceof \stdClass) {
                isset($json->name) && $this->name = $json->name;
                isset($json->description) && $this->description = $json->description;
                isset($json->host) && $this->host = $json->host;
                isset($json->auth) && $this->auth = strtoupper($json->auth) === 'YES';
            }
        }
        // 2. console
        $this->console->info("{$json->name}, {$json->description}");
        $this->console->info("需要鉴权: {$json->auth}");
        $this->console->info("域名前缀: {$json->host}");
        $this->console->info("扫描目录: %s", $this->controllerPath);
        // 3. 遍历目录
        $this->scanner($path.'/'.$this->controllerPath);
    }

    /**
     * 解析控制器
     */
    public function parser()
    {
        foreach ($this->classMap as $class) {
            $class = str_replace("/", "\\", $class);
            try {
                $controller = new Controller($this, $class);
                $controller->parser();
                $this->controllers[$class] = $controller;
            } catch(\Exception $e) {
                $this->console->error($e->getMessage());
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

    /**
     * 发布Markdown文档
     * 在Collectionk中发布README.md索引文档, 同时
     * 触发Controller的文档发布
     */
    public function toMarkdown()
    {
        // 1. title
        $text = '# '.$this->name;
        // 2. description
        if ($this->description !== '') {
            $text .= $this->eol.$this->description;
        }
        // 3. information
        $text .= $this->eol;
        $text .= '* **鉴权** : `'.(strtoupper($this->auth) === 'YES' ? '开启' : '关闭').'`'.$this->crlf;
        $text .= '* **域名** : `'.$this->schema.'://'.$this->host.'.'.$this->domain.'`'.$this->crlf;
        $text .= '* **导出** : `'.date('Y-m-d H:i').'`';
        // 4. index
        $text .= $this->eol;
        $text .= '### 接口目录'.$this->eol;
        foreach ($this->controllers as $controller) {
            if (count($controller->methods) === 0) {
                continue;
            }
            $name = trim($controller->annotation->name);
            $desc = preg_replace("/\n/", "", trim($controller->annotation->description));
            $url = str_replace('\\', '/', substr($controller->reflect->getName(), 16));
            $text .= '* ['.$name.'](./'.$url.'/README.md) : '.$desc.$this->crlf;
            $apis = $controller->getIndex(false);
            if ($apis !== '') {
                $text .= $apis.$this->crlf;
            }
        }
        // 5. code map
        $text .= $this->eol;
        $text .= '### 编码对照表';
        $text .= $this->eol;
        $text .= $this->getCodeMap();
        // 6. save README.md
        $this->saveMarkdown($this->basePath.'/'.$this->publishTo, 'README.md', $text);
        // 7. trigger controllers
        foreach ($this->controllers as $controller) {
            $controller->toMarkdown();
        }
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
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
