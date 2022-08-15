<?php

declare(strict_types=1);

namespace app\Process;

use Imi\Config;
use Imi\Event\Event;
use Imi\Swoole\Process\Annotation\Process;
use Imi\Swoole\Process\BaseProcess;
use Imi\Util\ImiPriority;

/**
 * @Process("TestProcess")
 */
class SwooleTestProcess extends BaseProcess
{
    public function run(\Swoole\Process $process): void
    {
        $channel = new \Swoole\Coroutine\Channel();
        Event::on('IMI.PROCESS.END', function () use ($channel) {
            $channel->push(1);
        }, ImiPriority::IMI_MAX);
        // @phpstan-ignore-next-line
        do
        {
            var_dump(__METHOD__, Config::get('nacos'));
        }
        while (!$channel->pop(3));
    }
}
