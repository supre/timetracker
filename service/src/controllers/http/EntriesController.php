<?php

namespace RoarProj\controllers\http;

use DateTime;
use Monolog\Logger;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as V;
use RoarProj\controllers\middlewares\JsonApiValidator;
use RoarProj\controllers\traits\UserPermissionValidator;
use RoarProj\entities\token\TokenFactory;
use RoarProj\entities\user\User;
use RoarProj\entities\user\UserRepository;
use RoarProj\exceptions\EntityNotFound;
use RoarProj\services\EntriesService;


class EntriesController implements ControllerProviderInterface
{
    use UserPermissionValidator;

    private const DATE_FORMAT = "Y-m-d";

    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->post(
            "/teams/{teamName}/users/{userId}/entries",
            $this->routeSaveEntry()
        )->before(
            $this->entryPayloadValidation()
        )->before($this->validateUserPermissions($this->adminOrSelf()));


        ///////////////////////////////////////////////////////////////////////////

        $controller->put(
            "/teams/{teamName}/users/{userId}/entries/{entryId}",
            $this->routeUpdateEntry()
        )->before(
            $this->entryPayloadValidation()
        )->before($this->validateUserPermissions($this->adminOrSelf()));


        ///////////////////////////////////////////////////////////////////////////

        $controller->delete(
            "/teams/{teamName}/users/{userId}/entries/{entryId}",
            $this->routeDeleteEntry()
        )->before($this->validateUserPermissions($this->adminOrSelf()));


        ///////////////////////////////////////////////////////////////////////////


        $controller->get(
            "/teams/{teamName}/users/{userId}/entries",
            $this->routeGetEntries()
        )->before($this->validateUserPermissions($this->adminOrSelf()));


        ///////////////////////////////////////////////////////////////////////////

        $controller->get(
            "/entries/download.php",
            $this->routeDownloadEntries()
        )->before(
            function (Request $request, Application $app) {
                $token = $request->query->get('token');
                $token = $this->getTokenFactory($app)->fromString($token);
                $user = $this->getUsersRepository($app)->getUserForToken($token);
                if (empty($user)) {
                    throw new AccessDeniedHttpException("Invalid token");
                }
                $request->attributes->set("user", $user);
            }
        );


        return $controller;
    }

    private function getLogger(Application $app)
    : Logger {
        return $app['logger'];
    }

    private function routeDownloadEntries()
    {
        return function (Request $request, Application $app) {
            $owner = $request->attributes->get('user');
            $team = $request->query->get('team');
            $this->verifyTeamOwnership($owner, $team);
            list($before, $after) = $this->getFilters($request);
            $user = (int)$request->query->get('user');

            $filename = 'TimeSheet.html';
            $fileContent = $this->getEntriesService($app)->getBinaryFileForUserEntries(
                $user,
                $team,
                $before,
                $after
            );
            $response = new Response($fileContent);
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            );
            $response->headers->set('Content-Disposition', $disposition);

            return $response;
        };
    }

    private function routeSaveEntry()
    {
        return function (
            Request $request,
            Application $app,
            $teamName,
            $userId
        ) {
            list($date, $notes, $hoursWorked) = $this->getEntryParams($request, $teamName);

            try {
                return $this->getEntriesService($app)->addEntryForUser($date, $notes, $hoursWorked, $userId, $teamName);
            } catch (EntityNotFound $e) {
                throw new NotFoundHttpException('Resource not found');
            }
        };
    }

    private function routeUpdateEntry()
    {
        return function (Request $request, Application $app, $teamName, $userId, $entryId) {
            list($date, $notes, $hoursWorked) = $this->getEntryParams($request, $teamName);

            try {
                return $this->getEntriesService($app)->updateEntryForUser(
                    $entryId,
                    $date,
                    $notes,
                    $hoursWorked,
                    $userId,
                    $teamName
                );
            } catch (EntityNotFound $e) {
                throw new NotFoundHttpException('Resource not found');
            }
        };
    }

    private function routeDeleteEntry()
    {
        return function (
            Request $request,
            Application $app,
            $teamName,
            $userId,
            $entryId
        ) {
            $owner = $request->attributes->get("user");
            $this->verifyTeamOwnership($owner, $teamName);

            try {
                $this->getEntriesService($app)->deleteEntryForUser($userId, $entryId, $teamName);
            } catch (EntityNotFound $e) {
                throw new NotFoundHttpException("Resource not found");
            }

            return new Response(null, Response::HTTP_ACCEPTED);
        };
    }

    private function routeGetEntries()
    {
        return function (
            Request $request,
            Application $app,
            $teamName,
            $userId
        ) {
            $owner = $request->attributes->get("user");
            $this->verifyTeamOwnership($owner, $teamName);

            list($before, $after) = $this->getFilters($request);

            try {
                return $this->getEntriesService($app)->getEntriesForUser($userId, $teamName, $before, $after);
            } catch (EntityNotFound $e) {
                $this->getLogger($app)->warn($e->getMessage());
                throw new NotFoundHttpException("Resource not found");
            }
        };
    }

    /**
     * @param Application $app
     * @return EntriesService
     */
    private function getEntriesService(Application $app)
    : EntriesService {
        /**
         * @var EntriesService $entriesService
         */
        $entriesService = $app['Entries.service'];
        return $entriesService;
    }

    /**
     * @param Application $app
     * @return UserRepository
     */
    private function getUsersRepository(Application $app)
    : UserRepository {
        return $app['User.repository'];
    }

    /**
     * @param Application $app
     * @return TokenFactory
     */
    private function getTokenFactory(Application $app)
    : TokenFactory {
        /**
         * @var TokenFactory $entriesService
         */
        return $app['Token.factory'];
    }

    private function entryPayloadValidation()
    {
        return new JsonApiValidator(
            'entry', [
                       JsonApiValidator::SCHEMA_ATTRIBUTES => new V\Collection(
                           [
                               'fields' => [
                                   'date'        => new V\Date(),
                                   'notes'       => new V\Type('string'),
                                   'hoursWorked' => new V\Range(['min' => 0.1, 'max' => 24])
                               ]
                           ]
                       )
                   ]
        );
    }

    /**
     * @return \Closure
     */
    private function adminOrSelf()
    : \Closure
    {
        return function (User $caller, Request $request) {
            $userId = $request->attributes->get("userId");
            $self = (int)$caller->getId() === (int)$userId;

            return $caller->isAdmin() || $self;
        };
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getFilters(Request $request)
    : array {
        $before = $request->query->get('date_before');
        $after = $request->query->get('date_after');


        if ($before) {
            $before = DateTime::createFromFormat(self::DATE_FORMAT, $before);
        }
        if ($after) {
            $after = DateTime::createFromFormat(self::DATE_FORMAT, $after);
        }
        return array($before, $after);
    }

    /**
     * @param Request $request
     * @param $teamName
     * @return array
     */
    private function getEntryParams(Request $request, $teamName)
    : array {
        $owner = $request->attributes->get("user");
        $this->verifyTeamOwnership($owner, $teamName);
        $attributes = new ParameterBag($request->attributes->get(JsonApiValidator::ATTR_FIELDS));
        $date = $attributes->get('date');
        $notes = $attributes->get('notes');
        $hoursWorked = $attributes->get('hoursWorked');

        $date = DateTime::createFromFormat(self::DATE_FORMAT, $date);
        $hoursWorked = (float)$hoursWorked;
        return array($date, $notes, $hoursWorked);
    }

}