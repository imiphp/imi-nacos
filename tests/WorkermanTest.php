<?php

declare(strict_types=1);

namespace Imi\Nacos\Test;

class WorkermanTest extends BaseTest
{
    protected string $registryServiceName = 'http';

    protected static function __startServer(): void
    {
        self::$process = $process = new \Symfony\Component\Process\Process([\PHP_BINARY, \dirname(__DIR__) . '/example/bin/imi-workerman', 'workerman/start'], null, [
            'IMI_CONFIG_CENTER_MODE' => \Imi\ConfigCenter\Enum\Mode::PROCESS,
        ]);
        $process->start();
    }
}
