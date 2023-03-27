<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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
     *
     * @ORM\Column(name="name_room", type="string", length=255, nullable=false)
     */
    private $nameRoom;

    /**
     * @var int
     *
     * @ORM\Column(name="area", type="integer", nullable=false)
     */
    private $area;

    /** 
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=false)
     */
    private $state;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

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
   

}
