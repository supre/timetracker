<?php


namespace RoarProj\exceptions;


class AlreadyExists extends BaseException
{
    public function __construct(
        string $entityType,
        $reason = '',
        $message = '',
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($reason, $message, $code, $previous);
        $this->entityType = $entityType;
    }

    /**
     * @return string
     */
    public function getEntityType()
    : string
    {
        return $this->entityType;
    }

    /**
     * @var string
     */
    private $entityType;
}