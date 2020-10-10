<?php

namespace RoarProj\exceptions;

class BaseException extends \Exception
{
    public function __construct(
        $reason = '',
        $message = '',
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct(
            $message,
            $code,
            $previous
        );

        $this->reason = $reason;
    }

    public function getReason()
    {
        return $this->reason;
    }

    private $reason;
}