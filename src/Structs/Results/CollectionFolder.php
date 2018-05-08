<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * 应用文件夹结构
 * <code>
 * CollectionFolder::factory([
 *     'name' => 'folder name',
 *     'description' => 'description of folder',
 *     'item' => [
 *         [
 *             // ...
 *         ]
 *     ]
 * ])
 * </code>
 * @package Uniondrug\Postman\Structs\Results
 */
class CollectionFolder extends Struct
{
    /**
     * 文件夹名称
     * @var string
     */
    public $name;
    /**
     * 文件夹描述
     * @var string
     */
    public $description;
    /**
     * API结构列表
     * @var Api[]
     */
    public $item;
}
