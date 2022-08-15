<?php

declare(strict_types=1);

namespace app\Command;

use Imi\Cli\Annotation\Command;
use Imi\Cli\Annotation\CommandAction;
use Imi\Cli\Contract\BaseCommand;
use Imi\Config;

/**
 * @Command("test")
 */
class TestCommand extends BaseCommand
{
    /**
     * @CommandAction(name="test")
     */
    public function test(): void
    {
        var_dump(Config::get('nacos'));
    }
}
