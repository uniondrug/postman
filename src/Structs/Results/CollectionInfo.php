<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-05-04
 */
namespace Uniondrug\Postman\Structs\Results;

use Uniondrug\Structs\Struct;

/**
 * 应用信息结构
 * <code>
 * CollectionInfo::factory([
 *    'name' => 'collection name',
 *    'description' => 'description of Collection info'
 * ]);
 * </code>
 * @package Uniondrug\Postman\Structs\Results
 */
class CollectionInfo extends Struct
{
    /**
     * 应用名称
     * @var string
     */
    public $name;
    /**
     * 应用描述
     * @var string
     */
    public $description;
    /**
     * 应用标准
     * @var string
     */
    public $schema = "https://schema.getpostman.com/json/collection/v2.1.0/collection.json";
}
