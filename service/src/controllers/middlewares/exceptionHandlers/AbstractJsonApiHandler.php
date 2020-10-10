<?php

namespace RoarProj\controllers\middlewares\exceptionHandlers;

use Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\ErrorCollection;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractJsonApiHandler
{

    public function __construct(Application $app)
    {
        $this->errors = new ErrorCollection();
        $this->headers = ['content-type' => 'application/vnd.api+json'];
        $this->statusCode = 500;
        $this->app = $app;
    }

    public function addError(Error $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @param ErrorInterface[]|ErrorCollection $errors
     */
    public function addErrors($errors)
    {
        foreach ($errors as $error) {
            $this->errors[] = $error;
        }
    }

    public function addHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function createResponse()
    {
        /** @var EncoderInterface $encoder */
        $encoder = $this->app['serialization.encoder'];
        $responseStr = $encoder->withJsonApiVersion()
                               ->encodeErrors($this->errors);

        return JsonResponse::fromJsonString(
            $responseStr,
            $this->getStatusCode(),
            $this->getHeaders()
        );
    }

    /**
     * @return Application
     */
    public function getApp()
    {
        return $this->app;
    }

    private $app;
    private $errors;
    private $headers;
    private $statusCode;
}
