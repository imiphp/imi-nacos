<?php

declare(strict_types=1);

namespace Imi\Nacos\Test;

class FpmTest extends BaseTest
{
    protected static function __startServer(): void
    {
        self::$process = $process = new \Symfony\Component\Process\Process([\PHP_BINARY, \dirname(__DIR__) . '/example/bin/imi-cli', 'fpm/start']);
        $process->start();
    }
}
