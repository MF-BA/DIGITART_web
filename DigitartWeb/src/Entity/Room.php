<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Room
 *
 * @ORM\Table(name="room")
 * @ORM\Entity
  * @ORM\Entity(repositoryClass="App\Repository\RoomRepository")
 */
class Room
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_room", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idRoom;

    /**
     * @var string
     * @Assert\NotBlank(message="Please enter a name for the room")
     * @Assert\Length(
     *     min = 3,
     *     max = 255,
     *     minMessage = "The name of the room must be at least {{ limit }} characters long",
     *     maxMessage = "The name of the room cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(name="name_room", type="string", length=255, nullable=false)
     */
    private $nameRoom;

    /**
     * @var int
     * @Assert\NotBlank(message="Please enter an area for the room")
     * @Assert\Positive(message="The area of the room must be a positive number")
     *
     * @ORM\Column(name="area", type="integer", nullable=false)
     */
    private $area;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="state", type="string", length=255, nullable=false)
     */
    private $state;

    /**
     * @var string|null
     * @Assert\NotBlank()
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

     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;
    
     /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    public function getIdRoom(): ?int
    {
        return $this->idRoom;
    }

    public function getNameRoom(): ?string
    {
        return $this->nameRoom;
    }

    public function setNameRoom(string $nameRoom): self
    {
        $this->nameRoom = $nameRoom;

        return $this;
    }

    public function getArea(): ?int
    {
        return $this->area;
    }

    public function setArea(int $area): self
    {
        $this->area = $area;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

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

    public function getCreatedAt(): ?\DateTimeInterface
{
    return $this->createdAt;
}

public function getUpdatedAt(): ?\DateTimeInterface
{
    return $this->updatedAt;
}


}
