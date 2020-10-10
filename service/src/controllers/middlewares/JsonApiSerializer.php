<?php

namespace RoarProj\controllers\middlewares;

use Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Document\Link;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;


class JsonApiSerializer
{

    public function __construct(Application $app)
    {
        $this->container = $app;
    }

    public function __invoke(
        $responsePayload,
        Request $request
    ) {
        if (is_null($responsePayload)) {
            throw new HttpException(404, 'Resource not found.');
        }

        /** @var EncoderInterface $encoder */
        $encoder = $this->container['serialization.encoder'];


        //Self link (link to current url basically)
        $encoder = $encoder->withLinks(
            [
                LinkInterface::SELF => $this->selfLinkFromRequest($request)
            ]
        );

        $isRelationship = $this->isRelationshipRequest($request);

        $params = $request->attributes->get(JsonApiValidator::ATTR_JSONAPI_PARAMS);

        try {
            if ($isRelationship) {
                $relatedLink = $this->relatedLinkFromRequest($request);

                $result = $encoder
                    ->withLinks(
                        [
                            LinkInterface::RELATED => $relatedLink
                        ]
                    )
                    ->encodeIdentifiers($responsePayload, $params);
            } else {
                $result = $encoder->encodeData($responsePayload, $params);
            }
        } catch (\InvalidArgumentException $e) {
            return $responsePayload;
        }

        switch ($request->getMethod()) {
            case Request::METHOD_POST :
                $statusCode = 201;
                break;

            case Request::METHOD_DELETE :
                $statusCode = 204;
                break;

            default :
                $statusCode = 200;
                break;
        }

        return JsonResponse::fromJsonString(
            $result,
            $statusCode,
            ['content-type' => 'application/vnd.api+json']
        );
    }

    private function selfLinkFromRequest(Request $request)
    {
        $uri = $request->getRequestUri();

        return new Link($uri, null, true);
    }

    private function relatedLinkFromRequest(Request $request)
    {
        $link = $this->selfLinkFromRequest($request);

        return new Link(
            str_replace('/relationships/', '/', $link->getSubHref()),
            $link->getMeta(),
            $link->isTreatAsHref()
        );
    }

    private function isRelationshipRequest(Request $request)
    {
        $path = $request->getRequestUri();

        $parts = explode('/', $path);

        $isRel = count($parts) > 2 &&
            ($parts[count($parts) - 2] == DocumentInterface::KEYWORD_RELATIONSHIPS);

        return $isRel;
    }

    private $container;
}