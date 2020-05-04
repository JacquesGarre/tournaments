<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlayerRepository")
 */
class Player
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $licence;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email_adress;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $points;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private $genre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $club;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Board", inversedBy="players")
     */
    private $boards;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Payment", mappedBy="player")
     */
    private $payments;

    /**
     * @ORM\Column(type="integer")
     */
    private $checkin_status;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tournament", inversedBy="players")
     */
    private $tournaments;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $bib_number;

    /**
     * @ORM\Column(type="integer")
     */
    private $valid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $validation_url;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Contest", mappedBy="players")
     */
    private $contests;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $trans_id;

    public function __construct()
    {
        $this->boards = new ArrayCollection();
        $this->matches = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->tournaments = new ArrayCollection();
        $this->contests = new ArrayCollection();
    }

    public function getId() : ? int
    {
        return $this->id;
    }

    public function getFirstname() : ? string
    {
        return $this->firstname;
    }

    public function setFirstname(? string $firstname) : self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function __toString()
    {
        return $this->lastname . ' ' . $this->firstname;
    }

    public function getLastname() : ? string
    {
        return $this->lastname;
    }

    public function setLastname(? string $lastname) : self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getLicence() : ? string
    {
        return $this->licence;
    }

    public function setLicence(int $licence) : self
    {
        $this->licence = $licence;

        return $this;
    }

    public function getEmailAdress() : ? string
    {
        return $this->email_adress;
    }

    public function setEmailAdress(string $email_adress) : self
    {
        $this->email_adress = $email_adress;

        return $this;
    }

    public function getPoints() : ? int
    {
        return $this->points;
    }

    public function setPoints(? int $points) : self
    {
        $this->points = $points;

        return $this;
    }

    public function getGenre() : ? string
    {
        return $this->genre;
    }

    public function setGenre(string $genre) : self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getClub() : ? string
    {
        return $this->club;
    }

    public function setClub(? string $club) : self
    {
        $this->club = $club;

        return $this;
    }

    /**
     * @return Collection|Board[]
     */
    public function getBoards() : Collection
    {
        return $this->boards;
    }

    public function addBoard(Board $board) : self
    {
        if (!$this->boards->contains($board)) {
            $this->boards[] = $board;
        }

        return $this;
    }

    public function removeBoard(Board $board) : self
    {
        if ($this->boards->contains($board)) {
            $this->boards->removeElement($board);
        }

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
            $payment->setPlayer($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment) : self
    {
        if ($this->payments->contains($payment)) {
            $this->payments->removeElement($payment);
            // set the owning side to null (unless already changed)
            if ($payment->getPlayer() === $this) {
                $payment->setPlayer(null);
            }
        }

        return $this;
    }

    public function getCheckinStatus() : ? int
    {
        return $this->checkin_status;
    }

    public function setCheckinStatus(int $checkin_status) : self
    {
        $this->checkin_status = $checkin_status;

        return $this;
    }

    /**
     * @return Collection|Tournament[]
     */
    public function getTournaments() : Collection
    {
        return $this->tournaments;
    }

    public function addTournament(Tournament $tournament) : self
    {
        if (!$this->tournaments->contains($tournament)) {
            $this->tournaments[] = $tournament;
        }

        return $this;
    }

    public function removeTournament(Tournament $tournament) : self
    {
        if ($this->tournaments->contains($tournament)) {
            $this->tournaments->removeElement($tournament);
        }

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function updateStatus(): self
    {

        $paymentsCount = $this->payments->count();
        $boardsCount = $this->boards->count();

        if($paymentsCount == 0) { //pas payé
            $this->status = 0;
        }

        if( // payé partiellement
            ($paymentsCount > 0) &&
            ($paymentsCount != $boardsCount) 
        ){
            $this->status = 2;
        }

        if( ($paymentsCount == $boardsCount) && $paymentsCount > 0){
            $p = 0;
            $np = 0;

            foreach($this->payments as $payment){
                if(!empty($payment->getTransaction()) && ($payment->getTransaction()->getStatus() == 1)){
                    $p++;
                } elseif(!empty($payment->getTransaction()) && ($payment->getTransaction()->getStatus() == 0) ) {
                    $np++;
                } elseif (empty($payment->getTransaction())) {
                    $p++;
                } else {
                    $np++;
                }
            }

            if ($np == 0) {
                $this->status = 1;
            } elseif ( $p > 0 && $np > 0) {
                $this->status = 2;
            } elseif ($p == 0){
                $this->status = 0;
            }
 

        }
        return $this;

    }

    public function getBibNumber(): ?int
    {
        return $this->bib_number;
    }

    public function setBibNumber(int $bib_number): self
    {
        $this->bib_number = $bib_number;

        return $this;
    }

    public function getValid(): ?int
    {
        return $this->valid;
    }

    public function setValid(int $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getValidationUrl(): ?string
    {
        return $this->validation_url;
    }

    public function setValidationUrl(string $validation_url): self
    {
        $this->validation_url = $validation_url;

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
            $contest->addPlayer($this);
        }

        return $this;
    }

    public function removeContest(Contest $contest): self
    {
        if ($this->contests->contains($contest)) {
            $this->contests->removeElement($contest);
            $contest->removePlayer($this);
        }

        return $this;
    }

    public function getTransId(): ?int
    {
        return $this->trans_id;
    }

    public function setTransId(?int $trans_id): self
    {
        $this->trans_id = $trans_id;

        return $this;
    }

}
