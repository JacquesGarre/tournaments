<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BoardRepository")
 */
class Board
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
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $min_points;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $max_points;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tournament", inversedBy="boards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tournament;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Payment", mappedBy="board", orphanRemoval=true)
     */
    private $payments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Player", mappedBy="boards")
     */
    private $players;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Contest", mappedBy="board", orphanRemoval=true)
     */
    private $contests;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->matches = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->contests = new ArrayCollection();
    }

    public function getId() : ? int
    {
        return $this->id;
    }

    public function getName() : ? string
    {
        return $this->name;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getPrice() : ? float
    {
        return $this->price;
    }

    public function setPrice(float $price) : self
    {
        $this->price = $price;

        return $this;
    }

    public function getMinPoints() : ? int
    {
        return $this->min_points;
    }

    public function setMinPoints(? int $min_points) : self
    {
        $this->min_points = $min_points;

        return $this;
    }

    public function getMaxPoints() : ? int
    {
        return $this->max_points;
    }

    public function setMaxPoints(? int $max_points) : self
    {
        $this->max_points = $max_points;

        return $this;
    }

    public function getTournament() : ? Tournament
    {
        return $this->tournament;
    }

    public function setTournament(? Tournament $tournament) : self
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getDate() : ? \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date) : self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection|Payment[]
     */
    public function getPayments() : Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment) : self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setBoard($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment) : self
    {
        if ($this->payments->contains($payment)) {
            $this->payments->removeElement($payment);
            // set the owning side to null (unless already changed)
            if ($payment->getBoard() === $this) {
                $payment->setBoard(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getPlayers() : Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player) : self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
            $player->addBoard($this);
        }

        return $this;
    }

    public function removePlayer(Player $player) : self
    {
        if ($this->players->contains($player)) {
            $this->players->removeElement($player);
            $player->removeBoard($this);
        }

        return $this;
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
            $contest->setBoard($this);
        }

        return $this;
    }

    public function removeContest(Contest $contest): self
    {
        if ($this->contests->contains($contest)) {
            $this->contests->removeElement($contest);
            // set the owning side to null (unless already changed)
            if ($contest->getBoard() === $this) {
                $contest->setBoard(null);
            }
        }

        return $this;
    }
}
