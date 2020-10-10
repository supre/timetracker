<?php


namespace RoarProj\services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use RoarProj\entities\user\User;
use RoarProj\entities\user\UserFactory;
use RoarProj\entities\user\UserRepository;
use RoarProj\exceptions\AlreadyExists;
use RoarProj\exceptions\EntityNotFound;
use RoarProj\exceptions\HasNoAccess;


class UserService
{
    public function __construct(
        UserFactory $userFactory,
        UserRepository $userRepository,
        EntityManager $entityManager
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->userFactory = $userFactory;
    }

    /**
     * @param $displayName
     * @param $email
     * @param $password
     * @param $team
     * @return User
     * @throws AlreadyExists
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAdminAccount($displayName, $email, $password, $team)
    {
        $teamExists = $this->userRepository->checkIfTeamExists($team);

        if ($teamExists) {
            throw new AlreadyExists("team");
        }

        // FIXME fix this to make sure only that email is rejected which is already used for an admin account
        $emailExists = $this->userRepository->checkIfEmailExists($email);

        if ($emailExists) {
            throw new AlreadyExists("email");
        }

        $user = $this->userFactory->create(
            $displayName,
            $password,
            $email,
            $team,
            User::ROLE_ADMIN
        );


        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param User $caller
     * @param $team
     * @param $email
     * @param $displayName
     * @param $password
     * @param $role
     * @return User
     * @throws AlreadyExists
     * @throws ORMException
     * @throws OptimisticLockException|HasNoAccess
     */
    public function addUserToTeam(User $caller, $team, $email, $displayName, $password, $role)
    {
        if ($this->userRepository->checkIfUserExistsForTeam($email, $team)) {
            throw new AlreadyExists("email");
        }

        if (!$this->canCallerAddOrEditThisRole($caller, $role)) {
            throw new HasNoAccess("Token doesn't have grant access to the role");
        }

        $user = $this->userFactory->create(
            $displayName,
            $password,
            $email,
            $team,
            $role
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param User $caller
     * @param int $userId
     * @param string $team
     * @param string $email
     * @param string $displayName
     * @param string $role
     * @param float|null $preferredWorkingHours
     * @return User
     * @throws HasNoAccess
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EntityNotFound
     */
    public function updateUser(
        User $caller,
        int $userId,
        string $team,
        string $email,
        string $displayName,
        ?string $role,
        ?float $preferredWorkingHours
    ) {
        if (!$this->canCallerAddOrEditThisRole($caller, $role)) {
            throw new HasNoAccess("Token doesn't have grant access to the role");
        }

        $user = $this->userRepository->getUserByIdOrFail($userId, $team);
        $user->setDisplayName($displayName);
        $user->setEmail($email);
        if (!empty($role)) {
            $user->setRole($role);
        }
        if (!empty($preferredWorkingHours)) {
            $user->setPreferredWorkingHours($preferredWorkingHours);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param int $userId
     * @param string $team
     * @param User $caller
     * @throws EntityNotFound
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EntityNotFound
     */
    public function deleteUserForId(int $userId, string $team, User $caller)
    {
        $user = $this->userRepository->getUserByIdOrFail($userId, $team);
        if (!$this->canCallerDeleteUser($caller, $user)) {
            return;
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    /**
     * @param $team
     * @return User[]
     */
    public function getUsersForTeam($team)
    : array {
        return $this->userRepository->getAllUsersForTeam($team);
    }

    /**
     * @param $userId
     * @param $team
     * @param $password
     * @return User
     * @throws EntityNotFound
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function changePassword($userId, $team, $password)
    {
        $user = $this->userRepository->getUserByIdOrFail($userId, $team);
        $user->setPassword($password);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function canCallerDeleteUser(User $caller, User $user)
    {
        // We dont delete the user if the caller is neither a user or a manager
        if (!$caller->isAdmin() && !$caller->isManager()) {
            return false;
        }

        // We don't delete if the to be deleted user an admin
        if ($user->isAdmin()) {
            return false;
        }

        // We don't delete the user if the caller is a manager and the to be deleted user is also a manager
        if ($user->isManager() && $caller->isManager()) {
            return false;
        }

        return true;
    }

    private function canCallerAddOrEditThisRole(User $caller, ?string $role)
    {
        // If role is empty we don't update it
        if (empty($role)) {
            return true;
        }

        // A manager can only add user role
        if ($caller->isManager() && in_array($role, [User::ROLE_MANAGER, User::ROLE_ADMIN])) {
            return false;
        }

        // Admin can not add another admin (forced constraint to simplify requirement)
        if ($caller->isAdmin() && $role === User::ROLE_ADMIN) {
            return false;
        }

        // A regular user shouldn't be able to add any user
        if (!$caller->isAdmin() && !$caller->isManager()) {
            return false;
        }

        return true;
    }

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserFactory
     */
    private $userFactory;

}