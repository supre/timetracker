<?php

namespace RoarProj\tests\services;


use Codeception\Stub;
use Codeception\Test\Unit;
use RoarProj\entities\token\TokenFactory;
use RoarProj\entities\user\User;
use RoarProj\entities\user\UserRepository;
use RoarProj\exceptions\EntityNotFound;
use RoarProj\services\AuthService;

class AuthServiceTest extends Unit
{
    public function testAccessTokenCanBeGenerated()
    {
        $displayName = 'displayName';
        $email = 'email';
        $password = 'password';
        $team = 'team';
        $role = User::ROLE_ADMIN;
        $id = 1;

        $user = $this->getUserStub($id, $displayName, $email, $password, $team, $role);

        $userRepository = Stub::make(
            UserRepository::class,
            [
                'getUserForCredentials' => $user
            ]
        );

        $authService = new AuthService($userRepository, new TokenFactory(30));
        $token = $authService->createTokenForCredentials('abc@test.com', 'abc@test.com', 'password');
        $access_token = $token->getAccessToken();
        $this->assertNotEmpty($access_token);

        $jwtChunks = explode('.', $access_token);
        $this->assertCount(3, $jwtChunks);
    }

    public function testDontGenerateTokenWhenIncorrectCredentials()
    {
        $userRepository = Stub::make(
            UserRepository::class,
            [
                'getUserForCredentials' => null
            ]
        );

        $authService = new AuthService($userRepository, new TokenFactory(30));
        $this->expectException(EntityNotFound::class);
        $authService->createTokenForCredentials('abc@test.com', 'abc@test.com', 'password');
    }


    /**
     * @param int $id
     * @param string $displayName
     * @param string $email
     * @param string $password
     * @param string $team
     * @param string $role
     * @return object
     * @throws \Exception
     */
    private function getUserStub(
        int $id,
        string $displayName,
        string $email,
        string $password,
        string $team,
        string $role
    )
    : object {
        $user = Stub::make(
            User::class,
            [
                'getId'          => $id,
                'getDisplayName' => $displayName,
                'getEmail'       => $email,
                'getPassword'    => $password,
                'getTeam'        => $team,
                'getRole'        => $role,
            ]
        );
        return $user;
    }


}
