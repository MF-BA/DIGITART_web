<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Auction
 *
 * @ORM\Table(name="auction", indexes={@ORM\Index(name="fk_artwork", columns={"id_artwork"})})
 * @ORM\Entity
 */
class Auction
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_auction", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idAuction;

    /**
     * @var int
     *
     * @ORM\Column(name="starting_price", type="integer", nullable=false)
     */
    private $startingPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="increment", type="integer", nullable=false, options={"default"="10"})
     */
    private $increment = 10;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ending_date", type="date", nullable=false)
     */
    private $endingDate;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="state", type="string", length=10, nullable=true)
     */
    private $state;

    /**
     * @var \Artwork
     *
     * @ORM\ManyToOne(targetEntity="Artwork")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_artwork", referencedColumnName="id_art")
     * })
     */
    private $idArtwork;

    public function getIdAuction(): ?int
    {
        return $this->idAuction;
    }

    public function getStartingPrice(): ?int
    {
        return $this->startingPrice;
    }

    public function setStartingPrice(int $startingPrice): self
    {
        $this->startingPrice = $startingPrice;

        return $this;
    }

    public function getIncrement(): ?int
    {
        return $this->increment;
    }

    public function setIncrement(int $increment): self
    {
        $this->increment = $increment;

        return $this;
    }

    public function getEndingDate(): ?\DateTimeInterface
    {
        return $this->endingDate;
    }

    public function setEndingDate(\DateTimeInterface $endingDate): self
    {
        $this->endingDate = $endingDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getIdArtwork(): ?Artwork
    {
        return $this->idArtwork;
    }

    public function setIdArtwork(?Artwork $idArtwork): self
    {
        $this->idArtwork = $idArtwork;

        return $this;
    }


}
