<?php

namespace RoarProj\controllers\middlewares\exceptionHandlers;

use Neomerx\JsonApi\Document\Error;
use Symfony\Component\HttpFoundation\Request;
use RoarProj\exceptions\ValidationException;

class ValidationErrorHandler extends AbstractJsonApiHandler
{
    public function __invoke(ValidationException $e, Request $request)
    {
        $this->setStatusCode(400);

        $errors = [];

        //Generic top-level validation exception.
        $this->addError(
            new Error(
                null,
                null,
                400,
                'VALIDATION_EXCEPTION',
                $e->getMessage(),
                null,
                null,
                null
            )
        );

        //Generate one JSONAPI error for each validation violation.
        foreach ($e->getViolations() as $violation) {
            $code = $violation->getcode();

            if (!$code) {
                $errorName = 'INVALID';
            } else {
                try {
                    $errorName = $violation
                        ->getConstraint()
                        ->getErrorName($code);
                } catch (\InvalidArgumentException $e) {
                    $errorName = $code;
                }
            }

            $this->addError(
                new Error(
                    null,
                    null,
                    400,
                    'VALIDATION_EXCEPTION_' . $errorName,
                    $violation->getMessage(),
                    null,
                    [
                        'cause'        => $violation->getCause(),
                        'internalCode' => $code ? $code : null,
                        'path'         => $violation->getPropertyPath()
                    ],
                    null
                )
            );
        }

        return $this->createResponse();
    }
}