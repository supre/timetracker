<?php

namespace RoarProj\providers;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Gedmo\DoctrineExtensions;
use Gedmo\Timestampable\TimestampableListener;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RoarProj\utils\DoctrineLogBridge;

class OrmProvider implements ServiceProviderInterface
{
    public function __construct(
        $dbHost,
        $dbPort,
        $dbSocket,
        $dbName,
        $dbUser,
        $dbPass
    ) {
        $this->dbHost = $dbHost;
        $this->dbPort = $dbPort;
        $this->dbSocket = $dbSocket;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;

        $this->dbCharset = 'utf8';
        $this->dbDriver = 'pdo_mysql';
        $this->dbServerVersion = '5.7';
        $this->entityPaths = [__DIR__ . '/../entities'];
        $this->generationMode = getenv('DOCTRINE_CODE_GENERATION');
        $this->proxyGenerationPath = __DIR__ . '/../.generated/proxies';
        $this->vendorPath = __DIR__ . '/../../vendor';
    }

    public function register(Container $container)
    {
        $container['orm.eventManager'] = function () {
            return new EventManager();
        };

        $container['orm.annotationDriverChain'] = function () {
            return new MappingDriverChain();
        };

        $container['orm.annotationReader'] = function (Container $container) {
            return new AnnotationReader();
        };

        /**
         * @param Container $container
         *
         * @return EntityManager
         */
        $container['orm'] = function (Container $container) {
            $orm = $this->createEntityManager($container);
            $orm->getConnection()->executeQuery(
                "SET wait_timeout = ?",
                [60],
                ['integer']
            );

            return $orm;
        };

        $container['db'] = function (Container $container) {
            return $container['orm']->getConnection();
        };
    }

    private function createEntityManager(Container $container)
    {
        list($connectionParams, $evm, $config) = $this->getOrmConfiguration($container);
        return EntityManager::create($connectionParams, $config, $evm);
    }

    private function getOrmConfiguration(Container $container)
    {
        AnnotationRegistry::registerFile(
            $this->vendorPath . '/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
        );

        $connectionParams = [
            'driver'        => $this->dbDriver,
            'dbname'        => $this->dbName,
            'user'          => $this->dbUser,
            'password'      => $this->dbPass,
            'charset'       => $this->dbCharset,
            'serverVersion' => $this->dbServerVersion
        ];

        if ($this->dbSocket) {
            $connectionParams['unix_socket'] = $this->dbSocket;
        } else {
            if ($this->dbHost) {
                $connectionParams['port'] = $this->dbPort;
                $connectionParams['host'] = $this->dbHost;
            }
        }

        /** @var EventManager $evm */
        $evm = $container['orm.eventManager'];

        /** @var AnnotationReader $annotationReader */
        $annotationReader = $container['orm.annotationReader'];

        /** @var MappingDriverChain $driverChain */
        $driverChain = $container['orm.annotationDriverChain'];

        DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
            $driverChain,
            $annotationReader
        );

        $annotationDriver = new AnnotationDriver(
            $annotationReader,
            $this->entityPaths
        );

        $driverChain->addDriver($annotationDriver, 'RoarProj');

        //Setup orm configuration
        $config = new Configuration();
        $config->setMetadataDriverImpl($driverChain);
        $config->setProxyDir($this->proxyGenerationPath);
        $config->setProxyNamespace('RoarProj\generated\proxies');

        $timestampableListener = new TimestampableListener();
        $timestampableListener->setAnnotationReader($annotationReader);
        $evm->addEventSubscriber($timestampableListener);

        if ($container->offsetExists('logger.doctrine')) {
            $logger = $container['logger.doctrine'];
            $config->setSQLLogger(new DoctrineLogBridge($logger));
        }

        return [$connectionParams, $evm, $config];
    }

    private $dbHost;
    private $dbPort;
    private $dbSocket;
    private $dbName;
    private $dbCharset;
    private $dbUser;
    private $dbPass;
    private $entityPaths;
    private $generationMode;
    private $vendorPath;
    private $proxyGenerationPath;
    private $dbDriver;
    private $dbServerVersion;
}
