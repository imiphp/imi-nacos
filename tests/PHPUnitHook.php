<?php

declare(strict_types=1);

namespace Imi\Nacos\Test;

use Imi\App;
use Imi\Cli\CliApp;
use PHPUnit\Runner\BeforeFirstTestHook;

class PHPUnitHook implements BeforeFirstTestHook
{
    public function executeBeforeFirstTest(): void
    {
        App::run('app', CliApp::class, static function () {
        });
    }
}
