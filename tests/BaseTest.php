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
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        self::$httpHost = env('HTTP_SERVER_HOST', 'http://127.0.0.1:8080/');
        static::__startServer();
        $httpRequest = new HttpRequest();
        for ($i = 0; $i < 60; ++$i)
        {
            sleep(1);
            if ('imi' === $r = $httpRequest->timeout(3000)->get(self::$httpHost)->body())
            {
                return;
            }
        }
        throw new \RuntimeException('Server started failed');
    }

    /**
     * This method is called after the last test of this test class is run.
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

        for ($i = 0; $i < 10; ++$i)
        {
            sleep(1);
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
