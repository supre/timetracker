<?php

namespace RoarProj\controllers\middlewares;

use Silex\Application;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use RoarProj\exceptions\ValidationException;

class RequestValidator
{

    const BODY_SCHEMA = 'body';
    const PATH_SCHEMA = 'path';
    const QUERY_SCHEMA = 'query';

    public function __construct($options = [])
    {
        $this->pathSchema = isset($options[self::PATH_SCHEMA]) ?
            $options[self::PATH_SCHEMA] : null;
        $this->bodySchema = isset($options[self::BODY_SCHEMA]) ?
            $options[self::BODY_SCHEMA] : null;
        $this->querySchema = isset($options[self::QUERY_SCHEMA]) ?
            $options[self::QUERY_SCHEMA] : null;
    }

    /**
     * @param Request $request
     * @param Application $app
     * @throws ValidationException
     */
    public function __invoke(Request $request, Application $app)
    {
        $validator = $app['validator'];

        //URL Parameters validation
        $this->performValidation(
            $request->attributes,
            $this->pathSchema,
            'Invalid URL attributes.',
            $validator
        );

        //Body validation
        $this->performValidation(
            $request->request,
            $this->bodySchema,
            'Invalid Request Body.',
            $validator
        );

        //MetricQuery string validation
        $this->performValidation(
            $request->query,
            $this->querySchema,
            'Invalid URL query string.',
            $validator
        );
    }

    /**
     * @param ParameterBag $parameterBag
     * @param $schema
     * @param $message
     * @param $validator
     * @throws ValidationException
     */
    private function performValidation(
        ParameterBag $parameterBag,
        $schema,
        $message,
        $validator
    ) {
        if ($schema !== null) {
            $errors = $validator->validate($parameterBag->all(), $schema);
            $this->throwIfErrors($errors, $message);
        }
    }

    /**
     * @param ConstraintViolationListInterface $errors
     * @param $msg
     * @throws ValidationException
     */
    private function throwIfErrors(
        ConstraintViolationListInterface $errors,
        $msg
    ) {
        if (count($errors) > 0) {
            throw new ValidationException($msg, $errors);
        }
    }

    private $pathSchema;
    private $bodySchema;
    private $querySchema;
}