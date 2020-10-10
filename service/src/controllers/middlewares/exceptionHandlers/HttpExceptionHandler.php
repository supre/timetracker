<?php

namespace RoarProj\controllers\middlewares\exceptionHandlers;

use Neomerx\JsonApi\Document\Error;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HttpExceptionHandler extends AbstractJsonApiHandler
{
    public function __invoke(HttpException $e, Request $request)
    {
        $this->addHeaders($e->getHeaders());
        $this->setStatusCode($e->getStatusCode());

        $this->addError(
            new Error(
                null,
                null,
                $e->getStatusCode(),
                null,
                $e->getMessage()
            )
        );

        return $this->createResponse();
    }
}