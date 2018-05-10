<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Commands;

use Uniondrug\Console\Command;
use Uniondrug\Postman\Parsers\Collection;

/**
 * 导出POSTMAN格式的API文档
 * @package Uniondrug\Postman\Commands
 */
class Postman extends Command
{
    /**
     * @inheritdoc
     */
    public function handle()
    {
        $path = getcwd();
        $postman = new Collection($path);
        $postman->parser();
        $contents = $postman->toPostman();

        if ($fp = @fopen('/Users/fuyibing/Desktop/export.json', 'wb+')) {
            fwrite($fp, $contents);
            fclose($fp);
            echo "[Exported]\n";
            exit;
        }

        echo $contents;
    }
}
