<?php


namespace RoarProj\services;


use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use RoarProj\entities\entries\EntriesRepository;
use RoarProj\entities\entries\Entry;
use RoarProj\entities\entries\EntryFactory;
use RoarProj\entities\user\User;
use RoarProj\entities\user\UserRepository;
use RoarProj\exceptions\EntityNotFound;


class EntriesService
{
    public function __construct(
        EntryFactory $entryFactory,
        UserRepository $userRepository,
        EntriesRepository $entriesRepository,
        EntityManager $entityManager,
        callable $template
    ) {
        $this->userRepository = $userRepository;
        $this->entryFactory = $entryFactory;
        $this->entityManager = $entityManager;
        $this->entriesRepository = $entriesRepository;
        $this->template = $template;
    }

    /**
     * @param int $userId
     * @param string $team
     * @param DateTime|null $before
     * @param DateTime|null $after
     * @return Entry[]
     * @throws EntityNotFound
     */
    public function getEntriesForUser(int $userId, string $team, ?DateTime $before, ?DateTime $after)
    : array {
        if ($before) {
            $before->setTime(0, 0);
        }
        if ($after) {
            $after->setTime(0, 0);
        }

        return $this->userRepository->getUserByIdOrFail($userId, $team)->getEntries($before, $after);
    }

    /**
     * @param int $userId
     * @param string $team
     * @param DateTime|null $before
     * @param DateTime|null $after
     * @return string
     * @throws EntityNotFound
     */
    public function getBinaryFileForUserEntries(int $userId, string $team, ?DateTime $before, ?DateTime $after)
    {
        $entries = array_map(
            function (Entry $entry) {
                return [
                    'hoursWorked' => $entry->getHoursWorked(),
                    'date'        => $entry->getDate(),
                    'notes'       => $entry->getNotes()
                ];
            },
            $this->getEntriesForUser(
                $userId,
                $team,
                $before,
                $after
            )
        );

        return ($this->template)(
            $entries,
            $after,
            $before
        );
    }

    /**
     * @param DateTime $date
     * @param string $notes
     * @param float $hoursWorked
     * @param int $userId
     * @param string $team
     * @return Entry
     * @throws ORMException|EntityNotFound
     */
    public function addEntryForUser(DateTime $date, string $notes, float $hoursWorked, int $userId, string $team)
    : Entry {
        $user = $this->userRepository->getUserByIdOrFail($userId, $team);
        return $this->persistEntryWithValues($date, $user, $notes, $hoursWorked);
    }

    /**
     * @param int $entryId
     * @param DateTime $date
     * @param string $notes
     * @param float $hoursWorked
     * @param int $userId
     * @param string $team
     * @return Entry
     * @throws EntityNotFound
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateEntryForUser(
        int $entryId,
        DateTime $date,
        string $notes,
        float $hoursWorked,
        int $userId,
        string $team
    )
    : Entry {
        $user = $this->userRepository->getUserByIdOrFail($userId, $team);
        $entry = $this->entriesRepository->findBy(['id' => $entryId, 'user' => $user]);
        if (empty($entry)) {
            throw new EntityNotFound("Entry not found", Entry::class);
        }

        return $this->persistEntryWithValues($date, $user, $notes, $hoursWorked);
    }

    /**
     * @param int $userId
     * @param int $entryId
     * @param string $team
     * @throws EntityNotFound
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteEntryForUser(int $userId, int $entryId, string $team)
    : void {
        $user = $this->userRepository->getUserByIdOrFail($userId, $team);
        $entry = $this->getEntryForUserById($entryId, $user);
        $user->deleteEntry($entry);
        $this->entityManager->flush();
    }

    /**
     * @param int $entryId
     * @param User $user
     * @return Entry
     * @throws EntityNotFound
     */
    private function getEntryForUserById(int $entryId, User $user)
    : Entry {
        $entry = $this->entriesRepository->findBy(['id' => $entryId, 'user' => $user]);
        if (empty($entry)) {
            throw new EntityNotFound("Entry not found", Entry::class);
        }
        return $entry[0];
    }

    /**
     * @param DateTime $date
     * @param User $user
     * @param string $notes
     * @param float $hoursWorked
     * @return Entry
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function persistEntryWithValues(DateTime $date, User $user, string $notes, float $hoursWorked)
    : Entry {
        $date->setTime(0, 0);
        $entry = $user->getEntryForDate($date);
        if (empty($entry)) {
            $entry = $this->entryFactory->createEmptyForDate($date);
            $user->addEntry($entry);
        }

        $entry->setNotes($notes);
        $entry->setHoursWorked($hoursWorked);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $entry;
    }

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntryFactory
     */
    private $entryFactory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntriesRepository
     */
    private $entriesRepository;

    /**
     * @var callable
     */
    private $template;

}