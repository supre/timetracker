<?php

/** @noinspection PhpUnusedAliasInspection */

namespace RoarProj\entities\user;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RoarProj\entities\entries\Entry;

/**
 * Class User
 *
 * @package ts\entities\user
 *
 * @ORM\Entity(repositoryClass="RoarProj\entities\user\UserRepository")
 * @ORM\Table(
 *     name="users"
 *     )
 */
class User
{
    const ROLE_ADMIN = "admin";
    const ROLE_MANAGER = "manager";
    const ROLE_USER = "user";
    const DATE_FORMAT = 'Y-m-d';

    public function __construct(string $displayName, string $password, string $email, string $team, string $role)
    {
        $this->displayName = $displayName;
        $this->password = $this->hashPassword($password);
        $this->email = $email;
        $this->role = $role;
        $this->team = $team;
        $this->entries = new ArrayCollection();
    }


    /**
     * @return mixed
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role): void
    {
        $this->role = $role;
    }

    /**
     * @param mixed $displayName
     */
    public function setDisplayName($displayName): void
    {
        $this->displayName = $displayName;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public static function isRoleValid(string $role)
    {
        return in_array($role, [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_USER]);
    }

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager()
    {
        return $this->role === self::ROLE_MANAGER;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @return mixed
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param DateTime|null $before
     * @param DateTime|null $after
     * @return array
     */
    public function getEntries(?DateTime $before, ?DateTime $after): array
    {
        return $this->entries->filter(
            function (Entry $entry) use ($before, $after) {
                $validBefore = empty($before) || $entry->dateBeforeOrEqual($before);
                $validAfter = empty($after) || $entry->dateAfterOrEqual($after);

                return $validBefore && $validAfter;
            }
        )->getValues();
    }

    public function addEntry(Entry $entry): void
    {
        $entry->setUser($this);
        $this->entries->add($entry);
    }

    /**
     * @param DateTime $date
     * @return Entry|null
     */
    public function getEntryForDate(DateTime $date): ?Entry
    {
        $entries = $this->entries->filter(
            function (Entry $entry) use ($date) {
                return $entry->getDate()->format(self::DATE_FORMAT) === $date->format(self::DATE_FORMAT);
            }
        );

        return $entries->isEmpty() ? null : $entries->first();
    }

    public function deleteEntry(Entry $entry)
    {
        $filteredEntries = $this->entries->filter(function(Entry $existingEntry) use ($entry){
            return $existingEntry->getId() === $entry->getId();
        });

        if ($filteredEntries->isEmpty()) return false;

        return $this->entries->removeElement($filteredEntries->first());
    }

    /**
     * @return float
     */
    public function getPreferredWorkingHours(): ?float
    {
        return $this->preferredWorkingHours;
    }

    /**
     * @param float $preferredWorkingHours
     */
    public function setPreferredWorkingHours(?float $preferredWorkingHours): void
    {
        $this->preferredWorkingHours = $preferredWorkingHours;
    }

    public function setPassword(string $password): void
    {
        $this->password = $this->hashPassword($password);
    }


    public function ifPasswordMatches($password): bool
    {
        return password_verify($password, $this->password);
    }

    private function hashPassword(string $textPassword): string
    {
        return password_hash($textPassword, PASSWORD_DEFAULT);
    }

    /**
     * @var integer
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var
     * @ORM\Column(
     *     type="string",
     *     length=50
     *     )
     */
    private $displayName;

    /**
     * @var
     * @ORM\Column(
     *     type="string",
     *     length=255
     *     )
     */
    private $email;

    /**
     * @var
     * @ORM\Column(
     *     type="string",
     *     length=255
     *     )
     */
    private $password;

    /**
     * @var
     * @ORM\Column(
     *     type="string",
     *     length=32
     *     )
     */
    private $role;

    /**
     * @var
     * @ORM\Column(
     *     type="string",
     *     length=32
     *     )
     */
    private $team;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="RoarProj\entities\entries\Entry",
     *     cascade={"persist", "remove", "merge"},
     *     mappedBy="user",
     *     fetch="EAGER",
     *     orphanRemoval=true
     *     )
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $entries;

    /**
     * @var float
     *
     * @ORM\Column(
     *     type="float",
     *     length=5,
     *     nullable=true
     * )
     */
    private $preferredWorkingHours;
}
