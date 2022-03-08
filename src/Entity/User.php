<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tournament", mappedBy="admins")
     */
    private $tournaments_managed;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    public function __construct()
    {
        parent::__construct();
        $this->roles = array('ROLE_USER', 'ROLE_PLAYER');
        $this->tournaments_managed = new ArrayCollection();
        $this->boards = new ArrayCollection();
        // your own logic
    }

    /**
     * @return Collection|Tournament[]
     */
    public function getTournamentsManaged(): Collection
    {
        return $this->tournaments_managed;
    }

    public function addTournamentsManaged(Tournament $tournamentsManaged): self
    {
        if (!$this->tournaments_managed->contains($tournamentsManaged)) {
            $this->tournaments_managed[] = $tournamentsManaged;
            $tournamentsManaged->addAdmin($this);
        }

        return $this;
    }

    public function removeTournamentsManaged(Tournament $tournamentsManaged): self
    {
        if ($this->tournaments_managed->contains($tournamentsManaged)) {
            $this->tournaments_managed->removeElement($tournamentsManaged);
            $tournamentsManaged->removeAdmin($this);
        }

        return $this;
    }

    /**
     * @return Collection|Board[]
     */
    public function getBoards(): Collection
    {
        return $this->boards;
    }

    public function addBoard(Board $board): self
    {
        if (!$this->boards->contains($board)) {
            $this->boards[] = $board;
            $board->addPlayer($this);
        }

        return $this;
    }

    public function removeBoard(Board $board): self
    {
        if ($this->boards->contains($board)) {
            $this->boards->removeElement($board);
            $board->removePlayer($this);
        }

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

}