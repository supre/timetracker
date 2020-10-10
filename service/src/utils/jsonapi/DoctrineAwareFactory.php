<?php

namespace RoarProj\utils\jsonapi;

use Neomerx\JsonApi\Factories\Factory;

class DoctrineAwareFactory extends Factory
{
    public function createContainer(array $providers = [])
    {
        return new DoctrineAwareContainer($this, $providers);
    }
}