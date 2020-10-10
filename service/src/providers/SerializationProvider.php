<?php

namespace RoarProj\providers;

use Neomerx\JsonApi\Encoder\EncoderOptions;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RoarProj\controllers\serializers\EntryToJson;
use RoarProj\controllers\serializers\TokenToJsonApi;
use RoarProj\controllers\serializers\UserToJsonApi;
use RoarProj\entities\entries\Entry;
use RoarProj\entities\token\Token;
use RoarProj\entities\user\User;
use RoarProj\utils\jsonapi\DoctrineAwareContainer;
use RoarProj\utils\jsonapi\DoctrineAwareFactory;

class SerializationProvider implements ServiceProviderInterface
{
    /**
     * SerializationProvider constructor.
     *
     * @param string $baseUrl The host name, used to generate URLs when
     *                        serializing responses.
     */
    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function register(Container $container)
    {
        $container['serialization.baseUrl'] = $this->baseUrl;

        $container['serialization.schemas'] = function (Container $c) {
            return [
                Token::class => TokenToJsonApi::class,
                User::class  => UserToJsonApi::class,
                Entry::class => EntryToJson::class
            ];
        };

        $container['serialization.encoder'] = function (Container $c) {
            $schemas = $c['serialization.schemas'];
            $baseUrl = $c['serialization.baseUrl'];

            $factory = new DoctrineAwareFactory();
            $container = new DoctrineAwareContainer($factory, $schemas);

            $options = new EncoderOptions(JSON_UNESCAPED_SLASHES, $baseUrl);

            $encoder = $factory->createEncoder($container, $options);

            return $encoder->withJsonApiVersion();
        };
    }

    private $baseUrl;
}