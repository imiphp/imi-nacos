<?php

declare(strict_types=1);

namespace app\Process;

use Imi\Config;
use Imi\Timer\Timer;
use Imi\Workerman\Process\Annotation\Process;
use Imi\Workerman\Process\BaseProcess;
use Workerman\Worker;

/**
 * @Process("TestProcess")
 */
class WorkermanTestProcess extends BaseProcess
{
    public function run(Worker $worker): void
    {
        Timer::tick(3000, function () {
            var_dump(__METHOD__, Config::get('nacos'));
        });
    }
}
