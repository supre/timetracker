<?php

namespace RoarProj\tests\services\entries;

use DateTime;
use RoarProj\entities\user\UserRepository;
use RoarProj\exceptions\EntityNotFound;

class GetEntriesForUserTest extends BaseEntriesTestClass
{
    public function testGetEntriesForAnExistingUser()
    {
        $user = $this->dummyUserEntityThatExistsInRepo();
        $entries = $this->service->getEntriesForUser($user->getId(), $user->getTeam(), null, null);

        $this->assertNotEmpty($entries);
        $this->assertCount(3, $entries);
    }

    public function testThrowExceptionForANonExistingUser()
    {
        $user = $this->dummyUserEntityThatDoesNotExistInRepo();

        $this->expectException(EntityNotFound::class);
        $this->expectExceptionMessage(UserRepository::USER_NOT_FOUND);

        $this->service->getEntriesForUser($user->getId(), $user->getTeam(), null, null);
    }

    public function testFilterNotesProperlyForBefore()
    {
        $user = $this->dummyUserEntityThatExistsInRepo();
        $entries = $this->service->getEntriesForUser(
            $user->getId(),
            $user->getTeam(),
            (new DateTime())->modify('-2 days'),
            null
        );

        $this->assertNotEmpty($entries);
        $this->assertCount(2, $entries);
    }

    public function testFilterNotesProperlyForAfter()
    {
        $user = $this->dummyUserEntityThatExistsInRepo();
        $entries = $this->service->getEntriesForUser(
            $user->getId(),
            $user->getTeam(),
            null,
            (new DateTime())->modify('-6 days')
        );

        $this->assertNotEmpty($entries);
        $this->assertCount(2, $entries);
    }

    public function testFilterNotesProperlyForBetween()
    {
        $user = $this->dummyUserEntityThatExistsInRepo();
        $entries = $this->service->getEntriesForUser(
            $user->getId(),
            $user->getTeam(),
            (new DateTime())->modify('-1 days'),
            (new DateTime())->modify('-8 days')
        );

        $this->assertNotEmpty($entries);
        $this->assertCount(2, $entries);
    }
}
