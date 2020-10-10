<?php

namespace RoarProj\providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RoarProj\entities\token\TokenFactory;
use RoarProj\services\AuthService;

class AuthProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['Auth.service'] = function ($container) {
            return new AuthService(
                $container['User.repository'],
                $container['Token.factory']
            );
        };

        $container['Token.factory'] = function ($container) {
            $tokenDuration = 21800;
            return new TokenFactory($tokenDuration);
        };
    }

    private $tokenDuration;
}
