<?php

declare(strict_types=1);

namespace Imi\Nacos\Test;

class SwooleWorkerTest extends BaseTest
{
    protected string $registryServiceName = 'main';

    protected static function __startServer(): void
    {
        self::$process = $process = new \Symfony\Component\Process\Process([\PHP_BINARY, \dirname(__DIR__) . '/example/bin/imi-swoole', 'swoole/start'], null, [
            'IMI_CONFIG_CENTER_MODE' => \Imi\ConfigCenter\Enum\Mode::WORKER,
        ]);
        $process->start();
    }

    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass(): void
    {
        if (!\extension_loaded('swoole'))
        {
            self::markTestSkipped('no swoole');
        }
        parent::setUpBeforeClass();
    }
}
