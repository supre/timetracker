<?php

namespace RoarProj\controllers\serializers;

use Neomerx\JsonApi\Schema\SchemaProvider;
use RoarProj\entities\token\Token;

class TokenToJsonApi extends SchemaProvider
{
    /**
     * @param Token $object
     * @return string
     */
    public function getId($object)
    {
        return null;
    }

    /**
     * @param Token $object
     * @return array
     */
    public function getAttributes($object)
    : array {
        return [
            "access_token"  => $object->getAccessToken(),
            "refresh_token" => $object->getRefreshToken()
        ];
    }

    /**
     * @return string
     */
    public function getResourceType()
    {
        return 'token';
    }
}
