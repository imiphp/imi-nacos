<?php

declare(strict_types=1);

use function Imi\env;
use Imi\Util\Imi;

$rootPath = \dirname(__DIR__) . '/';

return [
    'hotUpdate'    => [
        'status'    => false, // 关闭热更新去除注释，不设置即为开启，建议生产环境关闭

        // --- 文件修改时间监控 ---
        // 'monitorClass'    =>    \Imi\HotUpdate\Monitor\FileMTime::class,
        'timespan'    => 1, // 检测时间间隔，单位：秒

        // --- Inotify 扩展监控 ---
        // 'monitorClass'    =>    \Imi\HotUpdate\Monitor\Inotify::class,
        // 'timespan'    =>    1, // 检测时间间隔，单位：秒，使用扩展建议设为0性能更佳

        // 'includePaths'    =>    [], // 要包含的路径数组
        'excludePaths'    => [
            $rootPath . '.git',
            $rootPath . 'bin',
            $rootPath . 'logs',
        ], // 要排除的路径数组，支持通配符*
    ],
    'ConfigCenter' => [
        // 'mode'    => \Imi\ConfigCenter\Enum\Mode::WORKER, // 工作进程模式
        // 'mode'    => \Imi\ConfigCenter\Enum\Mode::PROCESS, // 进程模式
        'mode'    => env('IMI_CONFIG_CENTER_MODE', \Imi\ConfigCenter\Enum\Mode::PROCESS),
        'configs' => [
            'nacos' => [
                'driver'  => \Imi\Nacos\Config\NacosConfigDriver::class,
                // 客户端连接配置
                'client'  => [
                    'host'                => env('IMI_NACOS_HOST', '127.0.0.1'), // 主机名
                    'port'                => env('IMI_NACOS_PORT', 8848), // 端口号
                    'prefix'              => env('IMI_NACOS_PREFIX', '/'), // 前缀
                    'username'            => env('IMI_NACOS_USERNAME', 'nacos'), // 用户名
                    'password'            => env('IMI_NACOS_PASSWORD', 'nacos'), // 密码
                    'timeout'             => 60000, // 网络请求超时时间，单位：毫秒
                    'ssl'                 => false, // 是否使用 ssl(https) 请求
                    'authorizationBearer' => false, // 是否使用请求头 Authorization: Bearer {accessToken} 方式传递 Token，旧版本 Nacos 需要设为 true
                ],
                // 监听器配置
                'listener' => [
                    'timeout'        => 30000, // 配置监听器长轮询超时时间，单位：毫秒
                    'failedWaitTime' => 3000, // 失败后等待重试时间，单位：毫秒
                    'savePath'       => Imi::getRuntimePath('config-cache'), // 配置保存路径，默认为空不保存到文件。php-fpm 模式请一定要设置！
                    'fileCacheTime'  => 30, // 文件缓存时间，默认为0时不受缓存影响，此配置只影响 pull 操作。php-fpm 模式请一定要设置为大于0的值！
                ],
                // 配置项
                'configs' => [
                    'nacos' => [
                        'key'   => 'imi-nacos-key1',
                        'group' => 'imi',
                    ],
                ],
            ],
        ],
    ],
    'AutoRunProcessManager' => [
        'processes' => [
            'TestProcess',
        ],
    ],
];
