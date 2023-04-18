<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Artwork
 *
 * @ORM\Table(name="artwork", indexes={@ORM\Index(name="fk_art", columns={"id_room"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\ArtworkRepository")
 */
class Artwork
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_art", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idArt;

    /**
     * @var string
     * @Assert\NotBlank(message=" artworkName doit etre non vide")
     * @Assert\Length(
     *      min = 5,
     *      minMessage=" Enter artworkName with minimum 5 caracters"
     *
     *     )
     * @ORM\Column(name="artwork_name", type="string", length=255, nullable=false)
     */
    private $artworkName;

    /**
     * @var Users
      * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="id_artist", referencedColumnName="id")
     * })
     */
    private $idArtist;

    /**
     * @var string|null
     *
     * @ORM\Column(name="artist_name", type="string", length=255, nullable=true)
     */
    private $artistName;

    /**
     * @var \DateTime
     * @Assert\NotBlank(message="Date of artwork creation cannot be blank")
     * @Assert\LessThanOrEqual("today", message="Date of artwork creation cannot be in the future")
     * @ORM\Column(name="date_art", type="date", nullable=false)
     */
    private $dateArt;

    /**
     * @var string|null

     * @Assert\NotBlank(message="Artwork description cannot be blank")
     * @Assert\Length(
     *      min = 7,
     *      max = 65535,
     *      minMessage = "Artwork description must have at least {{ limit }} characters",
     *      maxMessage = "Artwork description cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

     /**
     * @ORM\OneToMany(targetEntity="ImageArtwork",mappedBy="idArt", orphanRemoval=true, cascade={"persist"})
     */
    private $images;

    /**
     * @var Room
     *
     * @ORM\ManyToOne(targetEntity="Room")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="id_room", referencedColumnName="id_room")
     * })
     */
    private $idRoom;


     /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;
    
     /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    private $ownerType;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getIdArt(): ?int
    {
        return $this->idArt;
    }

    public function getArtworkName(): ?string
    {
        return $this->artworkName;
    }

    public function setArtworkName(string $artworkName): self
    {
        $this->artworkName = $artworkName;

        return $this;
    }

    public function getIdArtist(): ?Users
    {
        return $this->idArtist;
    }

    public function setIdArtist(?Users $idArtist): self
    {
        $this->idArtist = $idArtist;

        return $this;
    }

    public function getArtistName(): ?string
    {
        return $this->artistName;
    }

    public function setArtistName(?string $artistName): self
    {
        $this->artistName = $artistName;

        return $this;
    }

    public function getDateArt(): ?\DateTimeInterface
    {
        return $this->dateArt;
    }

    public function setDateArt(\DateTimeInterface $dateArt): self
    {
        $this->dateArt = $dateArt;

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

  

    public function getIdRoom(): ?Room
    {
        return $this->idRoom;
    }

    public function setIdRoom(?Room $idRoom): self
    {
        $this->idRoom = $idRoom;

        return $this;
    }

    public function getOwnerType(): ?string
    {
        return $this->idArtist ? 'artist' : 'museum';
    }

    public function setOwnerType(string $ownerType): void
    {
        // Do nothing - this property is virtual and cannot be set
    }

     /**
     * @return Collection|ImageArtwork[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ImageArtwork $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setIdArt($this);
        }

        return $this;
    }

    public function removeImage(ImageArtwork $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getIdArt() === $this) {
                $image->setIdArt(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
{
    return $this->createdAt;
}

public function getUpdatedAt(): ?\DateTimeInterface
{
    return $this->updatedAt;
}
    
}
