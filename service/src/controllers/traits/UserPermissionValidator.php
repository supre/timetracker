<?php


namespace RoarProj\controllers\traits;


use Closure;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use RoarProj\entities\user\User;

trait UserPermissionValidator
{
    /**
     * @param callable $validator
     * @return Closure
     */
    public function validateUserPermissions(callable $validator)
    {
        return function (Request $request, Application $application) use ($validator) {
            /**
             * @var User $user
             */
            $user = $request->attributes->get('user');

            if (empty($user) || !$validator($user, $request)) {
                throw new AccessDeniedHttpException("No access");
            }
        };
    }

    /**
     * @param User $owner
     * @param $team
     */
    private function verifyTeamOwnership(User $owner, $team)
    : void {
        // Deny request if the authorized user has a different team from the team provided in the api call
        if ($owner->getTeam() !== $team) {
            throw new AccessDeniedHttpException("No access to the provided team");
        }
    }

}