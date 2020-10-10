<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Knp\Provider\ConsoleServiceProvider;
use Kurl\Silex\Provider\DoctrineMigrationsProvider;
use Monolog\Handler\StreamHandler;
use Silex\Provider\MonologServiceProvider;
use Symfony\Component\Dotenv\Dotenv;
use RoarProj\providers\LoggingProvider;
use RoarProj\providers\OrmProvider;

require_once __DIR__ . '/bootstrap.php';

$dotEnv = new Dotenv();
$dotEnv->load(__DIR__ . "/../.env");

$diContainer = new Silex\Application();

$diContainer['debug'] = true;

$diContainer->register(new MonologServiceProvider());
$diContainer->register(new LoggingProvider());


if (isset($_SERVER['DOCTRINE_TOOLS_LOGGING'])) {
    $diContainer->extend(
        'logger.primaryHandler',
        function ($handler, $app) {
            return new StreamHandler('php://stdout');
        }
    );
}

$diContainer->register(
    new OrmProvider(
        getenv('MYSQL_HOST'),
        getenv('MYSQL_PORT'),
        getenv('MYSQL_SOCKET'),
        getenv('MYSQL_DB_NAME'),
        getenv('MYSQL_USER'),
        getenv('MYSQL_PASS')
    )
);

$diContainer->register(
    new ConsoleServiceProvider(),
    [
        'console.name'              => 'RoarProjConsole',
        'console.version'           => '0.0.1',
        'console.project_directory' => __DIR__ . "/.."
    ]
);

$console = &$diContainer['console'];

$diContainer->register(
    new DoctrineMigrationsProvider($console),
    [
        'migrations.directory'  => __DIR__ . '/../migrations',
        'migrations.name'       => 'Roar Migrations',
        'migrations.namespace'  => 'Roar\migrations',
        'migrations.table_name' => 'doctrine_migrations'
    ]
);

$entityManager = $diContainer['orm'];

return ConsoleRunner::createHelperSet($entityManager);
