<?php


namespace RoarProj\controllers\http;


use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints as V;
use RoarProj\controllers\middlewares\JsonApiValidator;
use RoarProj\exceptions\EntityNotFound;
use RoarProj\services\AuthService;


class AuthController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        /**
         * @var ControllerCollection $controller
         */
        $controller = $app['controllers_factory'];

        $controller->post(
            '/auth/token',
            function (Request $request, Application $app) {
                $attributes = new ParameterBag($request->attributes->get(JsonApiValidator::ATTR_FIELDS));

                $team = $attributes->get('team');
                $email = $attributes->get('email');
                $password = $attributes->get('password');

                try {
                    return $this->getAuthService($app)->createTokenForCredentials($team, $email, $password);
                } catch (EntityNotFound $e) {
                    throw new BadRequestHttpException("User not found");
                }
            }
        )->before($this->tokenValidator());

        return $controller;
    }

    /**
     * @param Application $app
     * @return AuthService
     */
    private function getAuthService(Application $app)
    : AuthService {
        return $app['Auth.service'];
    }

    private function tokenValidator()
    {
        return new JsonApiValidator(
            'token', [
                       JsonApiValidator::SCHEMA_ATTRIBUTES => new V\Collection(
                           [
                               'fields' => [
                                   "team"     => new V\Type("string"),
                                   "email"    => new V\Email(),
                                   "password" => new V\Type("string")
                               ]
                           ]
                       )
                   ]
        );
    }
}