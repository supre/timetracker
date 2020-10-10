<?php

namespace RoarProj\controllers\middlewares\exceptionHandlers;

use Neomerx\JsonApi\Document\Error;
use RoarProj\controllers\middlewares\exceptionHandlers\handlerProperties\LogExceptionTrait;
use RoarProj\exceptions\AuthorizationException;

class AuthorizationExceptionHandler extends AbstractJsonApiHandler
{

    public function __invoke(AuthorizationException $e)
    {
        $this->setStatusCode(401);

        $this->addError(
            new Error(
                null,
                null,
                401,
                $e->getReason(),
                $e->getMessage()
            )
        );

        return $this->createResponse();
    }
}