<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\NotNull;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Event
 *
 * @ORM\Table(name="event", indexes={@ORM\Index(name="id_room", columns={"id_room"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups("events")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="event_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Event name cannot be empty")
     * @Assert\Length(min=3, max=50, minMessage="doit etre plus que 3", maxMessage="doit etre moins que 49")
     * @Groups("events")
     */
    private $eventName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date", nullable=false)
     * @Assert\NotNull(message="Start date cannot be empty")
     * @Groups("events")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date", nullable=false)
     * @Assert\NotBlank(message="End date cannot be empty")
     * @Assert\GreaterThanOrEqual(
     *     propertyPath="startDate",
     *     message="The end date must be greater than the start date."
     * )
     * @Groups("events")
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="nb_participants", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0, message="The number of participants should be greater than 0.")
     * @Assert\Type(type="numeric", message="The number of participants must be a valid number.")
     * @Groups("events")
     */
    private $nbParticipants;

    /**
     * @var string
     *
     * @ORM\Column(name="detail", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Event details cannot be empty")
     * @Assert\Length(min=5, max=500, minMessage="doit etre plus que 5", maxMessage="doit etre moins que 500")
     * @Groups("events")
     */
    private $detail;

    /**
     * @var int
     *
     * @ORM\Column(name="start_time", type="integer", nullable=false)
     * @Assert\NotNull(message="Start time cannot be empty")
     * @Assert\Range(min=0, max=23, notInRangeMessage="Start time must be between {{ min }} and {{ max }}")
     * @Groups("events")
     */
    private $startTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     * @Groups("events")
     */
    private $image;

    /**
     * @var Room
     *
     * @ORM\ManyToOne(targetEntity="Room")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_room", referencedColumnName="id_room")
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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Images", mappedBy="event", orphanRemoval=true, cascade={"persist"})
     */
    private $images;
    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     * @Groups("events")
     */
    private $color;
    /**
     * @ORM\OneToMany(targetEntity=Comments::class, mappedBy="event", orphanRemoval=true)
     */
    private $comments;

    /**
     * @return Collection|Comments[]
     */
    public function getComments(): Collection
    {
        return $this->comments ?? new ArrayCollection();
    }


    public function addComment(Comments $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setEvent($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getEvent() === $this) {
                $comment->setEvent(null);
            }
        }

        return $this;
    }
    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * @return Collection|Images[]
     */

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Images $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setEvent($this);
        }

        return $this;
    }

    public function removeImage(Images $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getEvent() === $this) {
                $image->setEvent(null);
            }
        }

        return $this;
    }
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(?string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getNbParticipants(): ?string
    {
        return $this->nbParticipants;
    }

    public function setNbParticipants(?string $nbParticipants): self
    {
        $this->nbParticipants = $nbParticipants;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(?string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function getStartTime(): ?int
    {
        return $this->startTime;
    }

    public function setStartTime(?int $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

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


    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }
}
