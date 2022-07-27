<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\LevelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    // @phpstan-ignore-next-line
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
    ]);

    $parameters->set(Option::SKIP, [
        '*/vendor/*',
        \Rector\Php71\Rector\FuncCall\CountOnNullRector::class,
        \Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector::class,
        \Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector::class,
        \Rector\Php70\Rector\FuncCall\RandomFunctionRector::class,
    ]);

    $parameters->set(Option::BOOTSTRAP_FILES, [
        __DIR__ . '/vendor/autoload.php',
    ]);

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/src',
    ]);

    $parameters->set(Option::FOLLOW_SYMLINKS, false);

    // Define what rule sets will be applied
    // @phpstan-ignore-next-line
    $containerConfigurator->import(LevelSetList::UP_TO_PHP_74);
};
