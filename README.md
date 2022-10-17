# imi-nacos

[![Latest Version](https://img.shields.io/packagist/v/imiphp/imi-nacos.svg)](https://packagist.org/packages/imiphp/imi-nacos)
[![Php Version](https://img.shields.io/badge/php-%3E=7.4-brightgreen.svg)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=4.8.0-brightgreen.svg)](https://github.com/swoole/swoole-src)
[![imi License](https://img.shields.io/badge/license-MulanPSL%202.0-brightgreen.svg)](https://github.com/imiphp/imi-nacos/blob/master/LICENSE)

## 介绍

此项目是 imi 框架的 Nacos 组件。

> 正在开发中，随时可能修改，请勿用于生产环境！

**支持的功能：**

* [x] 配置中心

* [x] 服务注册

## 安装

`composer require imiphp/imi-nacos:~2.1.0`

## 使用说明

### 配置中心

#### 配置

`@app.beans`：

```php
[
    'ConfigCenter' => [
        // 'mode'    => \Imi\ConfigCenter\Enum\Mode::WORKER, // 工作进程模式
        'mode'    => \Imi\ConfigCenter\Enum\Mode::PROCESS, // 进程模式
        'configs' => [
            'nacos' => [
                'driver'  => \Imi\Nacos\Config\NacosConfigDriver::class,
                // Nacos 客户端连接配置
                'client'  => [
                    'host'                => '127.0.0.1', // 主机名
                    'port'                => 8848, // 端口号
                    'prefix'              => '/', // 前缀
                    'username'            => 'nacos', // 用户名
                    'password'            => 'nacos', // 密码
                    'timeout'             => 60000, // 网络请求超时时间，单位：毫秒
                    'ssl'                 => false, // 是否使用 ssl(https) 请求
                    'authorizationBearer' => false, // 是否使用请求头 Authorization: Bearer {accessToken} 方式传递 Token，旧版本 Nacos 需要设为 true
                ],
                // 监听器配置
                'listener' => [
                    'timeout'         => 30000, // 配置监听器长轮询超时时间，单位：毫秒
                    'failedWaitTime'  => 3000, // 失败后等待重试时间，单位：毫秒
                    'savePath'        => Imi::getRuntimePath('config-cache'), // 配置保存路径，默认为空不保存到文件。php-fpm 模式请一定要设置！
                    'fileCacheTime'   => 30, // 文件缓存时间，默认为0时不受缓存影响，此配置只影响 pull 操作。php-fpm 模式请一定要设置为大于0的值！
                    'pollingInterval' => 10000, // 客户端轮询间隔时间，单位：毫秒
                ],
                // 配置项
                'configs' => [
                    'nacos' => [
                        'key'   => 'imi-nacos-key1',
                        'group' => 'imi',
                        'type'  => 'json', // 配置内容类型，Nacos >= 1.3 可以不配，由配置项类型智能指定
                    ],
                ],
            ],
        ],
    ],
]
```

#### 获取配置

```php
\Imi\Config::get('nacos'); // 对应 imi-nacos-key1
```

#### 写入配置

```php
/** @var \Imi\ConfigCenter\ConfigCenter $configCenter */
$configCenter = App::getBean('ConfigCenter');
$name = 'imi-nacos-key1';
$group = 'imi';
$type = 'json';
$value = json_encode(['imi' => 'niubi']);
$configCenter->getDriver('nacos')->push($name, $value, [
    'group' => $group,
    'type'  => $type,
]);
```

### 注册中心

#### 引入依赖

`composer require imiphp/imi-service`

#### 配置

`@app.beans`：

```php
[
    'ServiceRegistry' => [
        'drivers' => [
            [
                'driver' => \Imi\Nacos\Service\NacosServiceRegistry::class, // 驱动类名
                // 注册的服务列表
                'services' => [
                    'main', // 格式1：主服务器是 main，子服务器就是子服务器名
                    // 格式2：数组配置
                    [
                        // 所有参数按需设置
                        'server'     => 'main', // 主服务器是 main，子服务器就是子服务器名
                        // 'instanceId' => '实例ID',
                        'serviceId'  => 'main_test',
                        'weight'     => 1, // 权重
                        'uri'        => 'http://127.0.0.1:8080', // uri
                        // 'host'       => '127.0.0.1',
                        // 'port'       => 8080,
                        'metadata'   => [
                            // 'group' => 'DEFAULT_GROUP', // 分组
                            // 'namespaceId' => '', // 命名空间
                            // 'metadata' => [], // metadata
                            // 'ephemeral' => true, // 是否为临时实例
                        ],
                        // 'interface'  => 'eth0', // 网卡 interface 名，自动获取当前网卡IP时有效
                    ],
                ],
                'client' => [
                    // Nacos 客户端连接配置
                    'host'                => '127.0.0.1', // 主机名
                    'port'                => 8848, // 端口号
                    'prefix'              => '/', // 前缀
                    'username'            => 'nacos', // 用户名
                    'password'            => 'nacos', // 密码
                    'timeout'             => 60000, // 网络请求超时时间，单位：毫秒
                    'ssl'                 => false, // 是否使用 ssl(https) 请求
                    'authorizationBearer' => false, // 是否使用请求头 Authorization: Bearer {accessToken} 方式传递 Token，旧版本 Nacos 需要设为 true
                ],
                'heartbeat' => 3, // 心跳时间，单位：秒
            ],
        ],
    ],
]
```

### 服务发现（负载均衡）

#### 引入依赖

`composer require imiphp/imi-service`

#### 配置

`@app.beans`：

```php
[
    'ServiceDiscovery' => [
        'drivers' => [
            [
                'driver'       => \Imi\Nacos\Service\NacosServiceDiscoveryDriver::class, // 服务发现驱动
                // 'client' => \Imi\Service\Discovery\DiscoveryClient::class, // 服务发现客户端，如无必要不需要修改
                // 负载均衡配置
                'loadBalancer' => \Imi\Service\LoadBalancer\RandomLoadBalancer::class, // 负载均衡-随机
                // 'loadBalancer' => \Imi\Service\LoadBalancer\RoundRobinLoadBalancer::class, // 负载均衡-轮询
                // 'loadBalancer' => \Imi\Service\LoadBalancer\WeightLoadBalancer::class, // 负载均衡-权重
                // 发现服务列表
                'services' => [
                    'serviceName', // 改为你的服务名称
                ],
                'client' => [
                    // Nacos 客户端连接配置
                    'host'                => '127.0.0.1', // 主机名
                    'port'                => 8848, // 端口号
                    'prefix'              => '/', // 前缀
                    'username'            => 'nacos', // 用户名
                    'password'            => 'nacos', // 密码
                    'timeout'             => 60000, // 网络请求超时时间，单位：毫秒
                    'ssl'                 => false, // 是否使用 ssl(https) 请求
                    'authorizationBearer' => false, // 是否使用请求头 Authorization: Bearer {accessToken} 方式传递 Token，旧版本 Nacos 需要设为 true
                ],
                'cacheTTL' => 60, // 缓存时间，单位：秒。默认为60秒，设为0不启用缓存
            ],
        ],
    ],
]
```

#### 获取服务可用节点

```php
/** @var \Imi\Service\Discovery\ServiceDiscovery $serviceDiscovery */
$serviceDiscovery = \Imi\App::getBean('ServiceDiscovery');
$service = $serviceDiscovery->getInstance('服务名称');

$service->getInstanceId(); // 实例ID，string
$service->getServiceId(); // 服务ID，string
$service->getWeight(); // 权重，float
$service->getUri(); // \Imi\Util\Uri
$service->getMetadata(); // 元数据，数组

// 获取服务实例的ip及端口的常用写法
$uri = $service->getUri();
$host = $uri->getHost();
$port = $uri->getPort();
```

## 免费技术支持

QQ群：17916227 [![点击加群](https://pub.idqqimg.com/wpa/images/group.png "点击加群")](https://jq.qq.com/?_wv=1027&k=5wXf4Zq)，如有问题会有人解答和修复。

## 运行环境

* [PHP](https://php.net/) >= 7.4
* [Composer](https://getcomposer.org/) >= 2.0
* [Swoole](https://www.swoole.com/) >= 4.8.0
* [imi](https://www.imiphp.com/) >= 2.1

## 版权信息

`imi-nacos` 遵循 MulanPSL-2.0 开源协议发布，并提供免费使用。

## 捐赠

<img src="https://cdn.jsdelivr.net/gh/imiphp/imi@2.1/res/pay.png"/>

开源不求盈利，多少都是心意，生活不易，随缘随缘……
