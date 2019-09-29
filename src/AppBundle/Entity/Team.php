<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use AppBundle\Entity\Fields\AgeRangeField;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DoctrineORMTeamRepository")
 * @ORM\Table(name="team")
 */
class Team
{
    use AgeRangeField;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $name;

    /**
     * @var User[]|Collection
     *
     * @ORM\ManyToMany(
     *     targetEntity="AppBundle\Entity\User",
     *     mappedBy="teams")
     */
    public $users;

    public function __construct()
    {
        $this->ageRanges = [];
        $this->users = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return User[]|Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function addUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addTeam($this);
        }
    }

    public function removeUser(User $user): void
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeTeam($this);
        }
    }

    /**
     * @param User[]|Collection $users
     */
    public function setUsers($users): void
    {
        $this->users = $users;
        // foreach ($this->users as $user) {
        //     $user->removeTeam($this);
        // }
        // foreach ($users as $user) {
        //     $user->addTeam($this);
        // }
    }
}
