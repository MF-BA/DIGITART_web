<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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
     *
     * @ORM\Column(name="artwork_name", type="string", length=255, nullable=false)
     */
    private $artworkName;

    /**
     * @var int|null
     *
     * @ORM\Column(name="id_artist", type="integer", nullable=true)
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
     *
     * @ORM\Column(name="date_art", type="date", nullable=false)
     */
    private $dateArt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="image_art", type="string", length=255, nullable=true)
     */
    private $imageArt;

    /**
     * @var Room
     *
     * @ORM\ManyToOne(targetEntity="Room")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="id_room", referencedColumnName="id_room")
     * })
     */
    private $idRoom;

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

    public function getIdArtist(): ?int
    {
        return $this->idArtist;
    }

    public function setIdArtist(?int $idArtist): self
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

    public function getImageArt(): ?string
    {
        return $this->imageArt;
    }

    public function setImageArt(?string $imageArt): self
    {
        $this->imageArt = $imageArt;

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


}
