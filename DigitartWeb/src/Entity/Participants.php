<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Participants
 *
 * @ORM\Table(name="participants", indexes={@ORM\Index(name="id_event_const", columns={"id_event"})})
 * @ORM\Entity
  * @ORM\Entity(repositoryClass="App\Repository\ParticipantsRepository")
 */
class Participants
{
    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=false)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=false)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="adress", type="string", length=255, nullable=false)
     */
    private $adress;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=255, nullable=false)
     */
    private $gender;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_event", referencedColumnName="id")
     * })
     */
    private $idEvent;

    /**
     * @var Users
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     * })
     */
    private $idUser;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getIdEvent(): ?Event
    {
        return $this->idEvent;
    }

    public function setIdEvent(?Event $idEvent): self
    {
        $this->idEvent = $idEvent;

        return $this;
    }

    public function getIdUser(): ?Users
    {
        return $this->idUser;
    }

    public function setIdUser(?Users $idUser): self
    {
        $this->idUser = $idUser;

        return $this;
    }


}
