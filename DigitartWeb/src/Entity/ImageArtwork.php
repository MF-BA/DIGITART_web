<?php

namespace App\Entity;

use App\Repository\ImageArtworkRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;


/**
 * ImageArtwork
 *
 * @ORM\Table(name="ImageArtwork", indexes={@ORM\Index(name="fk_art", columns={"id_art"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\ImageArtworkRepository")
 */
class ImageArtwork
{
   /**
     * @var int
     *
     * @ORM\Column(name="id_img", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Artwork
     *
     * @ORM\ManyToOne(targetEntity="Artwork", inversedBy="images")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="id_art", referencedColumnName="id_art")
     * })
     */
    private $idArt;
   


    /**
     * @ORM\Column(nullable="true")
     */
    private ?string $imageName = null;

   
    public function getId(): ?int
    {
        return $this->id;
    }
    public function setIdArt(?Artwork $idArt): self
    {
        $this->idArt = $idArt;

        return $this;
    }
    public function getIdArt(): ?Artwork
    {
        return $this->idArt;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

   
}
