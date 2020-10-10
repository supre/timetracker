<?php

namespace RoarProj\controllers\middlewares\exceptionHandlers;

use Neomerx\JsonApi\Document\Error;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use RoarProj\controllers\middlewares\exceptionHandlers\handlerProperties\LogExceptionTrait;

class AccessDeniedExceptionHandler extends AbstractJsonApiHandler
{

    public function __invoke(AccessDeniedHttpException $e, Request $request)
    {
        $this->setStatusCode($e->getStatusCode());

        $this->addError(
            new Error(
                null,
                null,
                $e->getStatusCode(),
                'ACCESS_DENIED_EXCEPTION',
                'Access Denied',
                $e->getMessage(),
                null,
                null
            )
        );

        return $this->createResponse();
    }
}