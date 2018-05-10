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
    protected $signature = 'postman
                            {--mode=api : 发布markdown文档}
                            {--path=docs/api : markdown文档存储位置}';

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $path = getcwd();
        $collection = new Collection($path);
        $collection->parser();
        $this->asMarkdown($collection);
        $this->asPostman($collection);
    }

    private function asMarkdown(Collection $collection)
    {
        $collection->toMarkdown();
    }

    private function asPostman(Collection $collection)
    {
        $contents = $collection->toPostman();
        $collection->saveMarkdown($collection->basePath.'/'.$collection->publishPostmanTo, 'postman.json', $contents);
    }
}
