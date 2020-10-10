<?php

namespace RoarProj\tests\services\entries;

class DeleteEntryTest extends BaseEntriesTestClass
{
    public function testDeleteEntryForUser()
    {
        $user = $this->dummyUserEntityThatExistsInRepo();
        $entry = $this->oldExistingNote1();

        $this->service->deleteEntryForUser($user->getId(), $entry->getId(), $user->getTeam());
        $entries = $this->service->getEntriesForUser($user->getId(), $user->getTeam(), null, null);

        $this->assertCount(2, $entries);
    }
}
