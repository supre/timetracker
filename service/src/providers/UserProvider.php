<?php

namespace RoarProj\providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use RoarProj\entities\user\User;
use RoarProj\entities\user\UserFactory;
use RoarProj\services\UserService;


class UserProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['User.service'] = function (Container $container) {
            return new UserService($container['User.factory'], $container['User.repository'], $container['orm']);
        };

        $container['User.factory'] = function () {
            return new UserFactory();
        };

        $container['User.repository'] = function (Application $container) {
            return $container['orm']->getRepository(User::class);
        };
    }
}