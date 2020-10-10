<?php

namespace RoarProj\controllers\http;

use Closure;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\Validator\Constraints as V;
use RoarProj\controllers\middlewares\JsonApiValidator;
use RoarProj\controllers\traits\UserPermissionValidator;
use RoarProj\entities\user\User;
use RoarProj\exceptions\AlreadyExists;
use RoarProj\exceptions\EntityNotFound;
use RoarProj\exceptions\HasNoAccess;
use RoarProj\services\UserService;

class UserController implements ControllerProviderInterface
{
    use UserPermissionValidator;

    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        #########################################################################

        // FIXME post registration user should receive an email.
        $controller->post(
            '/users',
            $this->routeRegisterAdmin()
        )->before($this->getUserValidator(true, false));

        #########################################################################

        $controller->delete(
            "/teams/{team}/users/{userId}",
            $this->routeDeleteUser()
        )->before(
            $this->validateUserPermissions(
                function (User $caller) {
                    return $caller->isAdmin() || $caller->isManager();
                }
            )
        );

        #########################################################################

        $controller->get(
            "/teams/{team}/users",
            $this->routeGetUsersForTeam()
        )->before(
            $this->validateUserPermissions(
                function (User $user) {
                    return $user->isAdmin() || $user->isManager();
                }
            )
        );

        #########################################################################

        $controller->post(
            '/teams/{team}/users',
            $this->routeAddUserToTeam()
        )->before(
            $this->getUserValidator(false, false)
        )->before(
            $this->validateUserPermissions(
                function (User $user) {
                    return $user->isAdmin() || $user->isManager();
                }
            )
        );

        #########################################################################

        $controller->put(
            '/teams/{team}/users/{userId}',
            $this->routeUpdateUser()
        )->before(
            $this->getUserValidator(false, true)
        )->before(
            $this->validateUserPermissions(
                $this->managerAdminOrSelf()
            )
        );

        #########################################################################

        $controller->patch(
            '/teams/{team}/users/{userId}',
            $this->routePasswordChange()
        )->before(
            $this->getValidatorForPasswordChange()
        )->before(
            $this->validateUserPermissions(
                $this->managerAdminOrSelf()
            )
        );

        return $controller;
    }

    /**
     * @return callable
     */
    private function routeRegisterAdmin()
    : callable
    {
        return function (Request $request, Application $app) {
            $attributes = new ParameterBag(
                $request->attributes->get(JsonApiValidator::ATTR_FIELDS)
            );

            $displayName = $attributes->get('displayName');
            $email = $attributes->get('email');
            $password = $attributes->get('password');
            $team = $attributes->get('team');

            try {
                return $this->getUserService($app)->createAdminAccount($displayName, $email, $password, $team);
            } catch (AlreadyExists $e) {
                throw new PreconditionFailedHttpException(
                    sprintf("%s already exists", ucfirst($e->getEntityType()))
                );
            }
        };
    }

    /**
     * @return JsonApiValidator
     */
    private function getValidatorForPasswordChange()
    : JsonApiValidator
    {
        return new JsonApiValidator(
            'user', [
                      JsonApiValidator::SCHEMA_ATTRIBUTES => new V\Collection(
                          [
                              'fields' => [

                                  'password' => [
                                      new V\Type('string'),
                                      new V\Length(
                                          [
                                              'min' => 9
                                          ]
                                      )
                                  ]

                              ]
                          ]
                      )
                  ]
        );
    }

    /**
     * @param bool $optionalRole
     * @param bool $optionalPassword
     * @return JsonApiValidator
     */
    private function getUserValidator($optionalRole = true, $optionalPassword = true)
    : JsonApiValidator {
        $role = new V\Choice([User::ROLE_MANAGER, User::ROLE_USER]);

        if ($optionalRole) {
            $role = new V\Optional($role);
        }

        $password = [
            new V\Type('string'),
            new V\Length(
                [
                    'min' => 9
                ]
            ),
            new V\NotBlank()
        ];

        if ($optionalPassword) {
            $password = new V\Optional($password);
        }

        return new JsonApiValidator(
            'user', [
                      JsonApiValidator::SCHEMA_ATTRIBUTES => new V\Collection(
                          [
                              'fields' => [
                                  'team'                  => new V\Type('string'),
                                  'displayName'           => [
                                      new V\Type('string'),
                                      new V\Length(
                                          [
                                              'min' => 5,
                                              'max' => 50
                                          ]
                                      )
                                  ],
                                  'password'              => $password,
                                  'email'                 => new V\Email(),
                                  'role'                  => $role,
                                  'preferredWorkingHours' => new V\Optional(new V\Range(['min' => 0.1, 'max' => 24]))
                              ]
                          ]
                      )
                  ]
        );
    }

    /**
     * @param Request $request
     * @return ParameterBag
     */
    private function getRequestAttributes(Request $request)
    : ParameterBag {
        return new ParameterBag(
            $request->attributes->get(JsonApiValidator::ATTR_FIELDS)
        );
    }

    /**
     * @return callable
     */
    private function routeDeleteUser()
    : callable
    {
        return function (
            Request $request,
            Application $app,
            $team,
            $userId
        ) {
            $caller = $request->attributes->get('user');
            $this->verifyTeamOwnership($caller, $team);

            try {
                $this->getUserService($app)->deleteUserForId($userId, $team, $caller);
            } catch (EntityNotFound $e) {
                throw new NotFoundHttpException('Resource not found');
            }

            return new Response(null, Response::HTTP_ACCEPTED);
        };
    }

    /**
     * @return callable
     */
    private function routeGetUsersForTeam()
    : callable
    {
        return function (Request $request, Application $app, $team) {
            $caller = $request->attributes->get('user');
            $this->verifyTeamOwnership($caller, $team);

            return $this->getUserService($app)->getUsersForTeam($team);
        };
    }

    /**
     * @return callable
     */
    private function routeAddUserToTeam()
    : callable
    {
        return function (Request $request, Application $app, $team) {
            $attributes = $this->getRequestAttributes($request);
            $caller = $request->attributes->get('user');
            $team = $attributes->get('team');
            $this->verifyTeamOwnership($caller, $team);
            $email = $attributes->get('email');
            $displayName = $attributes->get('displayName');
            $password = $attributes->get('password');
            $role = $attributes->get('role');

            try {
                return $this->getUserService($app)->addUserToTeam(
                    $caller,
                    $team,
                    $email,
                    $displayName,
                    $password,
                    $role
                );
            } catch (HasNoAccess $e) {
                throw new AccessDeniedHttpException($e->getReason());
            } catch (AlreadyExists $e) {
                throw new ConflictHttpException("User is already in the team");
            }
        };
    }

    /**
     * @param Application $app
     * @return UserService
     */
    private function getUserService(Application $app)
    {
        return $app['User.service'];
    }

    private function routePasswordChange()
    {
        return function (Request $request, Application $app, $team, $userId) {
            $attributes = $this->getRequestAttributes($request);
            $caller = $request->attributes->get('user');
            $this->verifyTeamOwnership($caller, $team);
            $password = $attributes->get('password');

            try {
                return $this->getUserService($app)->changePassword(
                    $userId,
                    $team,
                    $password
                );
            } catch (HasNoAccess $e) {
                throw new AccessDeniedHttpException($e->getReason());
            } catch (EntityNotFound $e) {
                throw new NotFoundHttpException("User not found");
            }
        };
    }

    private function routeUpdateUser()
    {
        return function (Request $request, Application $app, $team, $userId) {
            $attributes = $this->getRequestAttributes($request);
            $caller = $request->attributes->get('user');
            $team = $attributes->get('team');
            $this->verifyTeamOwnership($caller, $team);
            $email = $attributes->get('email');
            $displayName = $attributes->get('displayName');
            $role = $attributes->get('role');
            $preferredWorkingHours = $attributes->get('preferredWorkingHours');

            try {
                return $this->getUserService($app)->updateUser(
                    $caller,
                    $userId,
                    $team,
                    $email,
                    $displayName,
                    $role,
                    $preferredWorkingHours
                );
            } catch (HasNoAccess $e) {
                throw new AccessDeniedHttpException($e->getReason());
            } catch (EntityNotFound $e) {
                throw new NotFoundHttpException("User not found");
            }
        };
    }

    /**
     * @return Closure
     */
    private function managerAdminOrSelf()
    : Closure
    {
        return function (User $user, Request $request) {
            $mUserId = $request->attributes->get('userId');
            $self = (int)$mUserId === (int)$user->getId();
            return $user->isAdmin() || $user->isManager() || $self;
        };
    }

}