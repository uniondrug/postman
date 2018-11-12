# POSTMAN

> 以命令行的模式, 一健导出`Markdown`文档、`Postman`接口工具、`SDK`。



### 字段配置

| 默认 | 名称 | 用途 |
| :-- | :-- | :-- |
| NO | auth | 鉴权状态<br />`NO`: 关闭<br />`YES`: 开启, 通过SDK访问时需要鉴权 |
| -- | name | 项目名称<br />导出文档在`POSTMAN`工具中显示的项目名称<br />注: 若不指定则使用应用配置项`app.appName`的值 |
| -- | description | 项目描述<br />导出文档在`POSTMAN`工具中显示的一段项目描述信息 |
| -- | host | 域名前缀<br />固定前缀后, 适配在`POSTMAN`工具中, 按环境选择切换域名后缀, 实现一份文档多环境共用 |
| -- | sdk | SDK类名<br />导出文档时, 同步导出`PHP`版本的Class/类, 实际应用时, 将导出的文件复制到[SDK](https://github.com/uniondrug/service-sdk)项目即可 |
| -- | sdkService | SDK服务名<br />在Consul数据中心注册的服务名称, 非特殊情况一律使用域名前缀 |
| -- | sdkLink | 出入参文档连接<br />在导出的SDK类中, 每个方法的PHPDOC片段中, 加入原始项目的连接地址前缀 |

```json
{
    "name" : "", 
    "description" : ""
}
```



### 如何导出

```php
php console postman
```

### 接受指令

1. [alias](#alias) - 
1. [exec](#exec) - 
1. [delete](#delete) - 
1. [get](#get) - 
1. [head](#head) - 
1. [ignore](#ignore) - 
1. [input](#input) - 
1. [mock](#mock) - 
1. [options](#options) - 
1. [output](#output) - 
1. [patch](#patch) - 
1. [post](#post) - 
1. [put](#put) - 
1. [route](#route) - 
1. [routeprefix](#routeprefix) - 
1. [sdk](#sdk) - 
1. [validator](#validator) - 
1. [var](#var) - 
1. [version](#version) - 


### alias

> desc

### exec

> desc

### delete

> desc

### get

> desc

### head

> desc

### ignore

> desc

### input

> desc

### mock

> desc

### options

> desc

### output

> desc

### patch

> desc

### post

> desc

### put

> desc

### route

> desc

### routeprefix

> desc

### sdk

> desc

### validator

> desc

### var

> desc

### version

> desc

