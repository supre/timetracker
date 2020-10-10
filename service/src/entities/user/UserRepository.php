<?php

namespace RoarProj\entities\user;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use RoarProj\entities\token\Token;
use RoarProj\exceptions\EntityNotFound;

class UserRepository extends EntityRepository
{
    const USER_NOT_FOUND = "User not found";

    public function getUserForToken(Token $token)
    {
        return $this->findOneBy(["email" => $token->getEmail(), "team" => $token->getTeam()]);
    }

    /**
     * @param string $team
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public function getUserForCredentials(string $team, string $email, string $password)
    : ?User {
        /**
         * @var User $user
         */
        $user = $this->findOneBy(["email" => $email, "team" => $team]);

        return !empty($user) && password_verify($password, $user->getPassword()) ? $user : null;
    }

    public function checkIfUserExistsForTeam(string $email, string $team)
    {
        return $this->count(['email' => $email, 'team' => $team]) > 0;
    }

    public function checkIfTeamExists(string $teamName)
    : bool {
        return $this->count(["team" => $teamName]) > 0;
    }

    public function checkIfEmailExists(string $email)
    : bool {
        return $this->count(["email" => $email]) > 0;
    }

    /**
     * @param string $team
     * @return User[]
     */
    public function getAllUsersForTeam(string $team)
    : array {
        return $this->findByTeam($team);
    }

    /**
     * @param int $id
     * @throws ORMException
     */
    public function deleteForId(int $id)
    {
        /**
         * @var User $user
         */
        $user = $this->find($id);
        $this->getEntityManager()->remove($user);
    }

    /**
     * @param int $userId
     * @param string $team
     * @return User
     * @throws EntityNotFound
     */
    public function getUserByIdOrFail(int $userId, string $team)
    : User {
        /**
         * @var User $user
         */
        $user = $this->findOneBy(['id' => $userId, 'team' => $team]);

        if (empty($user)) {
            throw new EntityNotFound(self::USER_NOT_FOUND, User::class);
        }

        return $user;
    }
}
