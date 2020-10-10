<?php

namespace RoarProj\controllers\middlewares\exceptionHandlers;

use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Encoder\Encoder;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ExceptionErrorHandler
{
    public function __invoke(FatalThrowableError $e, Request $request)
    {
        $error = new Error(
            null,
            null,
            500,
            null,
            "Internal server error."
        );

        return JsonResponse::fromJsonString(
            Encoder::instance()->withJsonAPIVersion()->encodeError($error),
            500,
            ['Content-Type' => 'application/vnd.api+json']
        );
    }
}