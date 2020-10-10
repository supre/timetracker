<?php


namespace RoarProj\services;


use RoarProj\entities\token\Token;
use RoarProj\entities\token\TokenFactory;
use RoarProj\entities\user\User;
use RoarProj\entities\user\UserRepository;
use RoarProj\exceptions\EntityNotFound;

class AuthService
{
    public function __construct(UserRepository $userRepository, TokenFactory $tokenFactory)
    {
        $this->userRepository = $userRepository;
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * @param string $team
     * @param string $email
     * @param string $password
     * @return Token
     * @throws EntityNotFound
     */
    public function createTokenForCredentials(string $team, string $email, string $password)
    : Token {
        $user = $this->userRepository->getUserForCredentials($team, $email, $password);

        if (!$user) {
            throw new EntityNotFound('User not found', User::class);
        }

        return $this->tokenFactory->createUserToken($user);
    }

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var TokenFactory
     */
    private $tokenFactory;
}