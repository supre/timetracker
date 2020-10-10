<?php

namespace RoarProj\entities\user;


use InvalidArgumentException;

class UserFactory
{
    public function create(string $displayName, string $password, string $email, string $team, string $role)
    : User {
        if (!User::isRoleValid($role)) {
            throw new InvalidArgumentException("Unknown role: $role");
        }

        return new User($displayName, $password, $email, $team, $role);
    }
}
