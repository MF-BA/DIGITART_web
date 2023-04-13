<?php

namespace App\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PhpParser\Node\Name;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Auction
 *
 * @ORM\Table(name="auction", indexes={@ORM\Index(name="fk_artwork", columns={"id_artwork"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\AuctionRepository")
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
    private $id_auction;

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
     * @Assert\GreaterThan("today", message="The ending date must be greater than or equal to tomorrow.")
     */
    private $endingDate;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     * @Assert\Length(min=10, minMessage="The description must exceed 10 characters long.")
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="state", type="string", length=10, nullable=true)
     */
    private $state = null ;

    /**
     * @ORM\Column(name="added", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    private $added;

    /**
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="update")
     */
    private $updated;

    /**
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted = null;

    /**
     * @var artwork
     *
     * @ORM\ManyToOne(targetEntity="Artwork")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_artwork", referencedColumnName="id_art")
     * })
     */
    private $artwork;

    public function getIdAuction(): ?int
    {
        return $this->id_auction;
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

    public function getartwork(): ?Artwork
    {
        return $this->artwork;
    }
    public function getAdded()
    {
        return $this->added;
    }
    public function getUpdated()
    {
        return $this->updated;
    }
    public function getDeleted(): ?DateTime
    {
        return $this->deleted;
    }

    public function setDeleted(?DateTime $deleted): self
    {
        $this->deleted = $deleted;
        return $this;
    }

    public function setartwork(?Artwork $artwork): self
    {
        $this->artwork = $artwork;
        return $this;
    }
}
