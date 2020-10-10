<?php

namespace RoarProj\exceptions;

class EntityNotFound extends \Exception
{

    /**
     * EntityNotFound constructor.
     *
     * @param string $message
     * @param int $entityType
     * @param array $context
     *
     *  An associative array with keys that can help determining how the
     *  entity was trying to be fetched.
     *
     * @param null $previous
     */
    public function __construct(
        $message,
        $entityType,
        array $context = [],
        $previous = null
    ) {
        $this->entityType = $entityType;
        $this->context = $context;

        parent::__construct($message, 0, $previous);
    }

    public static function fromClassNameAndId($className, $id, $previous = null)
    {
        return new self(
            "Could not find the $className entity with id: $id.",
            $className,
            ['id' => $id],
            $previous
        );
    }

    public function getEntityType()
    {
        return $this->entityType;
    }

    public function getContext()
    {
        return $this->context;
    }

    private $entityType;
    private $context;
}