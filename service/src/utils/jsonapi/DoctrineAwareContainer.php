<?php

namespace RoarProj\utils\jsonapi;

use Doctrine\Common\Util\ClassUtils;
use Neomerx\JsonApi\Schema\Container;

class DoctrineAwareContainer extends Container
{
    protected function getResourceType($resource)
    {
        return ClassUtils::getClass($resource);
    }
}