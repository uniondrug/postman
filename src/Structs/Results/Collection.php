<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * POSTMAN应用/Collection
 * <code>
 * Collection::factory([
 *     'info' => [
 *         // ...
 *     ],
 *     'item' => [
 *        [
 *            // ...
 *        ]
 *     ]
 * ]);
 * </code>
 * @package Uniondrug\Postman\Structs\Results
 */
class Collection extends Struct
{
    /**
     * 应用信息结构
     * @var CollectionInfo
     */
    public $info;
    /**
     * 文件夹数组
     * @var CollectionFolder[]
     */
    public $item;
}
