<?php

namespace RoarProj\controllers\serializers;

use Neomerx\JsonApi\Schema\SchemaProvider;
use RoarProj\entities\user\User;

/**
 * Class UserToJsonApi
 * @package RoarProj\controllers\serializers
 */
class UserToJsonApi extends SchemaProvider
{
    /**
     * @param User $user
     * @return string
     */
    public function getId($user)
    {
        return $user->getId();
    }

    /**
     * @param User $user
     * @return array
     */
    public function getAttributes($user)
    : array {
        return [
            'team'                  => $user->getTeam(),
            'displayName'           => $user->getDisplayName(),
            'email'                 => $user->getEmail(),
            'role'                  => $user->getRole(),
            'preferredWorkingHours' => $user->getPreferredWorkingHours()
        ];
    }

    public function getResourceType()
    {
        return 'User';
    }
}
