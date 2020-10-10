<?php
/** @noinspection ALL */


namespace RoarProj\tests\services\entries;


use Codeception\Stub;
use DateTime;
use Doctrine\ORM\EntityManager;
use RoarProj\entities\entries\EntriesRepository;
use RoarProj\entities\entries\Entry;
use RoarProj\entities\entries\EntryFactory;
use RoarProj\entities\user\User;
use RoarProj\entities\user\UserRepository;
use RoarProj\services\EntriesService;

class BaseEntriesTestClass extends \Codeception\Test\Unit
{

    protected function setUp()
    {
        $this->service = new EntriesService(
            new EntryFactory(),
            $this->mockUserRepository(),
            $this->mockEntriesRepository(),
            $this->mockEntityManager(),
            $this->callableTemplate()
        );
    }

    protected function tearDown()
    {
        $this->existingUser = null;
    }


    protected function oldExistingNote1()
    : Entry
    {
        return Stub::construct(
            Entry::class,
            [
                (new DateTime())->modify('-7 days'),
                "Dummy note",
                "8"
            ],
            [
                'getId' => function () { return 1; }
            ]
        );
    }

    protected function oldExistingNote2()
    : Entry
    {
        return Stub::construct(
            Entry::class,
            [
                (new DateTime())->modify('-3 days'),
                "Dummy note",
                "8"
            ],
            [
                'getId' => function () { return 2; }
            ]
        );
    }

    protected function newExistingNote()
    : Entry
    {
        return Stub::construct(
            Entry::class,
            [
                new DateTime(),
                "Dummy note",
                "4"
            ],
            [
                'getId' => function () { return 3; }
            ]
        );
    }

    protected function dummyUserEntityThatExistsInRepo()
    : User
    {
        if (!empty($this->existingUser)) {
            return $this->existingUser;
        }

        $this->existingUser = Stub::construct(
            User::class,
            [
                'user',
                'password',
                'user@email.test',
                "team",
                User::ROLE_ADMIN
            ],
            [
                'getId' => function () { return 1; }
            ]
        );

        $this->existingUser->addEntry($this->oldExistingNote1());
        $this->existingUser->addEntry($this->oldExistingNote2());
        $this->existingUser->addEntry($this->newExistingNote());

        return $this->existingUser;
    }

    protected function dummyUserEntityThatDoesNotExistInRepo()
    : User
    {
        return Stub::construct(
            User::class,
            [
                'non_existing_user',
                'non_existing_password',
                'non_existing_user@email.test',
                "non_existing_team",
                User::ROLE_ADMIN
            ],
            [
                'getId' => function () { return 99999; }
            ]
        );
    }

    protected function mockUserRepository()
    : UserRepository
    {
        return Stub::make(
            UserRepository::class,
            [
                'findOneBy' => function ($params) {
                    list ($userId, $team) = array_values($params);

                    $user = $this->dummyUserEntityThatExistsInRepo();

                    $id = $user->getId();

                    if ($user->getTeam() === $team && $user->getId() === $userId) {
                        return $user;
                    }

                    return null;
                }
            ]
        );
    }

    protected function mockEntriesRepository()
    : EntriesRepository
    {
        return Stub::make(
            EntriesRepository::class,
            [
                'findBy' => function ($params) {
                    list($entryId, $user) = array_values($params);
                    $existingUser = $this->dummyUserEntityThatExistsInRepo();

                    if ($user->getId() !== $existingUser->getId()) {
                        return [];
                    }

                    $entries = $existingUser->getEntries(null, null);
                    return array_filter(
                        $entries,
                        function (Entry $entry) use ($entryId) {
                            return $entry->getId() === $entryId;
                        }
                    );
                }
            ]
        );
    }

    protected function mockEntityManager()
    : EntityManager
    {
        return Stub::make(
            EntityManager::class,
            [
                'persist' => function ($object) { return true; },
                'flush'   => function () { return true; },
            ]
        );
    }

    protected function callableTemplate()
    : callable
    {
        return function (...$args) {
            print_r($args);
        };
    }

    protected $existingUser;
    protected $service;

}