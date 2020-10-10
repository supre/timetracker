<?php

namespace RoarProj\exceptions;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Wrapper around Symfony constraint violation errors to throw them as
 * exceptions. They can be caught by the \ts\middlewares\ValidationErrorHandler
 * to convert them into nice JSONAPI validation error payloads.
 */
class ValidationException extends \Exception
{
    public function __construct(
        $message,
        ConstraintViolationListInterface $constraintViolations = null,
        $code = 0,
        $previous = null
    ) {
        $this->constraintViolations = $constraintViolations ?: new ConstraintViolationList();

        parent::__construct($message, $code, $previous);
    }

    public function getViolations()
    {
        return $this->constraintViolations;
    }

    private $constraintViolations;
}
