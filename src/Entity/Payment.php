<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Board", inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $board;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="payments")
     */
    private $player;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Transaction", inversedBy="payments")
     */
    private $transaction;

    public function getId() : ? int
    {
        return $this->id;
    }

    public function getValue() : ? float
    {
        return $this->value;
    }

    public function setValue(float $value) : self
    {
        $this->value = $value;

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

    public function getBoard() : ? Board
    {
        return $this->board;
    }

    public function setBoard(? Board $board) : self
    {
        $this->board = $board;

        return $this;
    }

    public function getPlayer() : ? Player
    {
        return $this->player;
    }

    public function setPlayer(? Player $player) : self
    {
        $this->player = $player;

        return $this;
    }

    public function getTransaction(): ? Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(? Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }
}
