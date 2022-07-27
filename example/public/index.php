<?php

declare(strict_types=1);

use Imi\App;
use Imi\AppContexts;
use Imi\Fpm\FpmApp;

require_once \dirname(__DIR__, 2) . '/vendor/autoload.php';

App::set(AppContexts::APP_PATH, \dirname(__DIR__), true);
App::run('app', FpmApp::class);
