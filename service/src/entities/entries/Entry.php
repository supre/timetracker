<?php
/** @noinspection PhpUnusedAliasInspection */


namespace RoarProj\entities\entries;


use DateTime;
use RoarProj\entities\user\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Emtry
 *
 * @package ts\entities\entries
 *
 * @ORM\Entity(repositoryClass="RoarProj\entities\entries\EntriesRepository")
 * @ORM\Table(
 *     name="entries"
 *     )
 */
class Entry
{
    public function __construct(DateTime $date, ?string $notes, ?float $hoursWorked)
    {
        $this->date = $date;
        $this->notes = $notes;
        $this->hoursWorked = $hoursWorked;
    }

    /**
     * @return int
     */
    public function getId()
    : int
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    : DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return bool
     */
    public function dateBeforeOrEqual(DateTime $date): bool {
        return $this->getDate() <= $date;
    }

    /**
     * @param DateTime $date
     * @return bool
     */
    public function dateAfterOrEqual(DateTime $date): bool {
        return $this->getDate() >= $date;
    }

    /**
     * @return string
     */
    public function getNotes()
    : string
    {
        return $this->notes;
    }

    public function setNotes(string $notes)
    : void {
        $this->notes = $notes;
    }

    /**
     * @return float
     */
    public function getHoursWorked()
    : float
    {
        return $this->hoursWorked;
    }

    public function setHoursWorked(float $hoursWorked)
    : void {
        $this->hoursWorked = $hoursWorked;
    }

    public function setUser(User $owner)
    {
        $this->user = $owner;
    }

    public function unsetUser()
    {
        $this->user = null;
    }

    /**
     * @var integer
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var DateTime
     *
     * @ORM\Column(
     *     type="datetime",
     * )
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(
     *     type="text",
     * )
     */
    private $notes;

    /**
     * @var float
     *
     * @ORM\Column(
     *     name="hours_worked",
     *     type="float",
     *     length=5)
     */
    private $hoursWorked;

    /**
     * @var User
     *
     * @ORM\ManyToOne(
     *     targetEntity="RoarProj\entities\user\User",
     *     inversedBy="entries"
     * )
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    private $user;
}