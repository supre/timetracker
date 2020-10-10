<?php

namespace RoarProj\controllers\middlewares;


use Silex\Application;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use RoarProj\entities\token\TokenFactory;
use RoarProj\entities\user\UserRepository;

class TokenValidator
{
    public function __construct(TokenFactory $tokenFactory, UserRepository $userRepository, array $skipRoutes)
    {
        $this->skipRoutes = $skipRoutes;
        $this->tokenFactory = $tokenFactory;
        $this->userRepository = $userRepository;
    }

    public function __invoke(Request $request, Application $app)
    {
        $apacheRequestHeaders = apache_request_headers();
        $headers = new ParameterBag($apacheRequestHeaders);

        $request->attributes->set("hasToken", false);

        $authorization = $headers->get('Authorization', '');
        $authorizationComponents = explode(' ', $authorization);

        // Ignore if there is no token available or non bearer token available
        if (count($authorizationComponents) < 2
            || $authorizationComponents[0] != 'Bearer'
            || empty($authorizationComponents[1])) {
            return;
        }

        $tokenString = $authorizationComponents[1];
        $token = $this->tokenFactory->fromString($tokenString);
        $user = $this->userRepository->getUserForToken($token);

        // Only throw an exception if an incorrect bearer token is provided
        if (empty($user)) {
            throw new AccessDeniedHttpException("Invalid token");
        }

        $request->attributes->set("user", $user);
        $request->attributes->set("hasToken", true);
    }

    /**
     * @var array
     */
    private $skipRoutes;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var UserRepository
     */
    private $userRepository;
}