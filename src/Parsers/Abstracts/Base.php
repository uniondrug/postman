<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-09
 */
namespace Uniondrug\Postman\Parsers\Abstracts;

use Uniondrug\Postman\Parsers\Annotation;

/**
 * 解析基类
 * @package Uniondrug\Postman\Parsers\Abstracts
 */
abstract class Base
{
    /**
     * @var Annotation
     */
    public $annotation;
    /**
     * 换行符
     * @var string
     */
    public $crlf = "\n";
    public $eol = "\n\n";
    public $schema = '{{protocol}}';
    public $domain = '{{domain}}';
    public $token = '{{token}}';

    public function __construct()
    {
    }

    /**
     * 保存Markdown文档
     */
    public function saveMarkdown($path, $name, $contents)
    {
        $file = $path.'/'.$name;
        try {
            if (!is_dir($path)) {
                mkdir($path, 0777);
            }
            $fp = fopen($file, 'wb+');
            fwrite($fp, $contents);
            fclose($fp);
        } catch(\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }
}
