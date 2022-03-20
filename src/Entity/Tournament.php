<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TournamentRepository")
 */
class Tournament
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="tournaments_managed")
     */
    private $admins;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Board", mappedBy="tournament", orphanRemoval=true)
     */
    private $boards;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\TournamentForm", mappedBy="tournament", cascade={"persist", "remove"})
     */
    private $tournamentForm;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Player", mappedBy="tournaments")
     */
    private $players;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $admin_email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Contest", mappedBy="tournament", orphanRemoval=true)
     */
    private $contests;

    /**
     * @ORM\Column(type="boolean")
     */
    private $onlinePaymentActivated;

    public function __construct()
    {
        $this->admins = new ArrayCollection();
        $this->boards = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->matches = new ArrayCollection();
        $this->contests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getAdmins(): Collection
    {
        return $this->admins;
    }

    public function addAdmin(User $admin): self
    {
        if (!$this->admins->contains($admin)) {
            $this->admins[] = $admin;
        }

        return $this;
    }

    public function removeAdmin(User $admin): self
    {
        if ($this->admins->contains($admin)) {
            $this->admins->removeElement($admin);
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
            $board->setTournament($this);
        }

        return $this;
    }

    public function removeBoard(Board $board): self
    {
        if ($this->boards->contains($board)) {
            $this->boards->removeElement($board);
            // set the owning side to null (unless already changed)
            if ($board->getTournament() === $this) {
                $board->setTournament(null);
            }
        }

        return $this;
    }

    public function getTournamentForm(): ?TournamentForm
    {
        return $this->tournamentForm;
    }

    public function setTournamentForm(TournamentForm $tournamentForm): self
    {
        $this->tournamentForm = $tournamentForm;

        // set the owning side of the relation if necessary
        if ($this !== $tournamentForm->getTournament()) {
            $tournamentForm->setTournament($this);
        }

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
            $player->addTournament($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): self
    {
        if ($this->players->contains($player)) {
            $this->players->removeElement($player);
            $player->removeTournament($this);
        }

        return $this;
    }

    public function getAdminEmail(): ?string
    {
        return $this->admin_email;
    }

    public function setAdminEmail(string $admin_email): self
    {
        $this->admin_email = $admin_email;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection|Contest[]
     */
    public function getContests(): Collection
    {
        return $this->contests;
    }

    public function addContest(Contest $contest): self
    {
        if (!$this->contests->contains($contest)) {
            $this->contests[] = $contest;
            $contest->setTournament($this);
        }

        return $this;
    }

    public function removeContest(Contest $contest): self
    {
        if ($this->contests->contains($contest)) {
            $this->contests->removeElement($contest);
            // set the owning side to null (unless already changed)
            if ($contest->getTournament() === $this) {
                $contest->setTournament(null);
            }
        }

        return $this;
    }

    public function getOnlinePaymentActivated(): ?bool
    {
        return $this->onlinePaymentActivated;
    }

    public function setOnlinePaymentActivated(bool $onlinePaymentActivated): self
    {
        $this->onlinePaymentActivated = $onlinePaymentActivated;
        return $this;
    }

}
