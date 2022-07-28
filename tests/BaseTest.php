<?php

declare(strict_types=1);

namespace Imi\Nacos\Test;

use function Imi\env;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Yurun\Util\HttpRequest;

abstract class BaseTest extends TestCase
{
    protected static Process $process;

    protected static string $httpHost = '';

    protected static function __startServer(): void
    {
        throw new \RuntimeException('You must implement the __startServer() method');
    }

    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass(): void
    {
        self::$httpHost = env('HTTP_SERVER_HOST', 'http://127.0.0.1:8080/');
        static::__startServer();
        $httpRequest = new HttpRequest();
        for ($i = 0; $i < 60; ++$i)
        {
            sleep(1);
            if ('imi' === $httpRequest->timeout(3000)->get(self::$httpHost)->body())
            {
                return;
            }
        }
        throw new \RuntimeException('Server started failed');
    }

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass(): void
    {
        if (isset(self::$process))
        {
            self::$process->stop();
        }
    }

    public function testSetAndGet(): void
    {
        $httpRequest = new HttpRequest();
        $value = ['value' => uniqid('', true)];
        $response = $httpRequest->post(self::$httpHost . '/set', [
            'name'  => 'imi-nacos-key1',
            'group' => 'imi',
            'value' => json_encode($value),
            'type'  => 'json',
        ]);

        $cacheFileName = \dirname(__DIR__) . '/example/.runtime/config-cache/imi/imi-nacos-key1';
        for ($i = 0; $i < 15; ++$i)
        {
            sleep(1);
            if (is_file($cacheFileName))
            {
                unlink($cacheFileName);
            }
            $response = $httpRequest->get(self::$httpHost . '/get');
            if ([
                'config' => $value,
            ] === $response->json(true))
            {
                $this->assertTrue(true);

                return;
            }
        }

        $this->assertTrue(false);
    }
}
