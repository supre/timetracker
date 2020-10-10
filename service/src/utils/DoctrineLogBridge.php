<?php


namespace RoarProj\utils;

use Doctrine\DBAL\Logging\SQLLogger;
use Monolog\Logger;

class DoctrineLogBridge implements SQLLogger
{

    public function __construct(Logger $monologLogger)
    {
        $this->monolog = $monologLogger;
    }

    public function startQuery(
        $query = null,
        array $params = null,
        array $types = null
    ) {
        $logObj = [
            'query'      => $query,
            'parameters' => $params,
            'types'      => $types
        ];

        $this->monolog->debug(print_r($logObj, true));
    }

    public function stopQuery() { }

    private $monolog;

}