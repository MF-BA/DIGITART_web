<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Bid
 *
 * @ORM\Table(name="bid")
 * @ORM\Entity
 */
class Bid
{
    /**
     * @var int
     *
     * @ORM\Column(name="ID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var int
     *
     * @ORM\Column(name="offer", type="integer", nullable=false)
     */
    private $offer;

    /**
     * @var Auction
     *
     * @ORM\ManyToOne(targetEntity="Auction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_auction", referencedColumnName="id_auction")
     * })
     */
    private $id_auction;

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getOffer(): ?int
    {
        return $this->offer;
    }

    public function setOffer(int $offer): self
    {
        $this->offer = $offer;

        return $this;
    }

    public function getIdAuction(): ?Auction
    {
        return $this->id_auction;
    }

    public function setIdAuction(Auction $id_auction): self
    {
        $this->id_auction = $id_auction;

        return $this;
    }

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function setIdUser(int $idUser): self
    {
        $this->idUser = $idUser;

        return $this;
    }


}
