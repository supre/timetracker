<?php

require_once __DIR__ . '/../config/bootstrap.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Knp\Console\Application as Console;
use Knp\Provider\ConsoleServiceProvider;
use Kurl\Silex\Provider\DoctrineMigrationsProvider;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @var Application $app
 */
$app = require_once __DIR__ . '/app.php';

$app->register(
    new ConsoleServiceProvider(),
    [
        'console.name'              => 'RoarProj',
        'console.version'           => '0.0.1',
        'console.project_directory' => __DIR__ . "/.."
    ]
);

/**
 * @var Console $console
 */
$console = &$app['console'];

$app->register(
    new DoctrineMigrationsProvider($console),
    [
        'migrations.directory'  => __DIR__ . '/../migrations',
        'migrations.name'       => 'Roar Migrations',
        'migrations.namespace'  => 'RoarProj\migrations',
        'migrations.table_name' => 'doctrine_migrations'
    ]
);

$console->setHelperSet(ConsoleRunner::createHelperSet($app['orm']));
ConsoleRunner::addCommands($console);

$dispatcher = new EventDispatcher();
$console->setDispatcher($dispatcher);

try {
    $console->run();
} catch (\Exception $e) {
    echo "\n" . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
