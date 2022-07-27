<?php

declare(strict_types=1);

namespace app;

use Imi\Main\AppBaseMain;
use Yurun\Util\YurunHttp;

class Main extends AppBaseMain
{
    public function __init(): void
    {
        // YurunHttp::setDefaultHandler(\Yurun\Util\YurunHttp\Handler\Curl::class);
    }
}
