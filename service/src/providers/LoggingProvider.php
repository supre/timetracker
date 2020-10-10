<?php

namespace RoarProj\providers;

use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Processor\UidProcessor;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider\MonologServiceProvider;

class LoggingProvider implements ServiceProviderInterface
{
    const LOGGING_CONTEXT_MAIN = 'main';
    const LOGGING_CONTEXT_DOCTRINE = 'doctrine';

    /**
     * @param Container $container
     *
     * @throws Exception
     */
    public function register(Container $container)
    {
        $container->register(new MonologServiceProvider());

        $container['logger.streamHandler'] = function ($app) {
            return new StreamHandler('php://stdout');
        };

        $container['logger.syslogHandler'] = function ($app) {
            return new SyslogHandler('application');
        };

        $container['logger.primaryFormatter'] = function ($app) {
            return $this->createFormatter();
        };

        $container->extend(
            'monolog',
            function ($monolog, $app) {
                $streamHandler = $app['logger.streamHandler'];
                $streamHandler->setFormatter($app['logger.primaryFormatter']);
                $monolog->pushHandler($streamHandler);

                $syslogHandler = $app['logger.syslogHandler'];
                $syslogHandler->setFormatter($app['logger.primaryFormatter']);
                $monolog->pushHandler($syslogHandler);

                return $monolog;
            }
        );

        $container['logger.doctrine'] = function ($app) {
            $logger = $app['monolog']->withName('doctrine');
            $logger->pushProcessor(new UidProcessor(32));

            return $logger;
        };
    }

    /**
     * @param string $outputFormat
     * @return LineFormatter
     */
    protected function createFormatter(string $outputFormat = null)
    {
        $formatter = new LineFormatter($outputFormat);
        $formatter->allowInlineLineBreaks();

        return $formatter;
    }


}