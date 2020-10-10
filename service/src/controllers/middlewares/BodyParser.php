<?php

namespace RoarProj\controllers\middlewares;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BodyParser
{
    public function __invoke(Request $request, Application $app)
    {
        $contentType = $request->headers->get('Content-Type');

        if ($this->isJsonBasedContentType($contentType) &&
            !empty($request->getContent())
        ) {
            $parsedBody = json_decode($request->getContent(), true);

            //Json decoding failed
            if ($parsedBody == null) {
                throw new BadRequestHttpException('Could not parse JSON body. Invalid JSON.');
            }

            $request->request->replace(
                is_array($parsedBody) ? $parsedBody : []
            );
        }
    }

    private function isJsonBasedContentType($contentType)
    {
        return preg_match('/application\/([a-z\.]+\+)?json/', $contentType) == 1;
    }
}
