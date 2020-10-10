<?php

namespace RoarProj\tests\services\entries;

use DateTime;
use RoarProj\entities\entries\Entry;
use RoarProj\entities\user\User;
use RoarProj\exceptions\EntityNotFound;

class AddEntryTest extends BaseEntriesTestClass
{
    public function testEntryObjectReceivedForValueValues()
    {
        $user = $this->dummyUserEntityThatExistsInRepo();

        $now = new DateTime();
        $notes = "Some dummy notes";
        $hoursWorked = 5;
        $userId = $user->getId();
        $team = $user->getTeam();


        $entry = $this->service->addEntryForUser(
            $now,
            $notes,
            $hoursWorked,
            $userId,
            $team
        );

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertEquals($entry->getNotes(), $notes);
        $this->assertEquals($entry->getDate()->format(User::DATE_FORMAT), $now->format(User::DATE_FORMAT));
        $this->assertEquals($entry->getHoursWorked(), $hoursWorked);
    }

    public function testExceptionThrownWhenUserNotFound()
    {
        $user = $this->dummyUserEntityThatDoesNotExistInRepo();

        $now = new DateTime();
        $notes = "Some dummy notes";
        $hoursWorked = 5;
        $userId = $user->getId();
        $team = $user->getTeam();

        $this->expectException(EntityNotFound::class);
        $this->expectExceptionMessage("User not found");

        $entry = $this->service->addEntryForUser(
            $now,
            $notes,
            $hoursWorked,
            $userId,
            $team
        );
    }
}
