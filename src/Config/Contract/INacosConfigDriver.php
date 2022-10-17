<?php

declare(strict_types=1);

namespace Imi\Nacos\Config\Contract;

use Imi\ConfigCenter\Contract\IConfigDriver;
use Yurun\Nacos\Client;

if (interface_exists(IConfigDriver::class))
{
    interface INacosConfigDriver extends IConfigDriver
    {
        /**
         * {@inheritDoc}
         */
        public function getOriginClient(): Client;
    }
}
