<?php

namespace RoarProj\controllers\middlewares\exceptionHandlers;

use Neomerx\JsonApi\Exceptions\JsonApiException;
use Symfony\Component\HttpFoundation\Request;

class JsonApiExceptionHandler extends AbstractJsonApiHandler
{
    public function __invoke(JsonApiException $e, Request $request)
    {
        $this->setStatusCode($e->getHttpCode());
        $this->addErrors($e->getErrors());
        return $this->createResponse();
    }
}