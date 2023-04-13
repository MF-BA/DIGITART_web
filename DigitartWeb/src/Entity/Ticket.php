<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations
/**
 * Ticket
 *
 * @ORM\Table(name="ticket")
 * @ORM\Entity
 */
class Ticket
{
    /**
     * @var int
     *
     * @ORM\Column(name="ticket_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $ticketId;

    /**
     * @var \DateTime|null
     * @Assert\NotBlank(message="Date should not be empty")
     * @ORM\Column(name="ticket_date", type="date", nullable=true)
     */
    private $ticketDate;

    /**
     * @var \DateTime|null
     * @Assert\NotBlank(message="Date should not be empty")
     * @Assert\GreaterThanOrEqual(propertyPath="ticketDate", message="The end date must be greater than the start date.")
     * @ORM\Column(name="ticket_edate", type="date", nullable=true)
     */
    private $ticketEdate;

    /**
     * @var int
     * @Assert\NotBlank(message="Price should not be empty !")
     * @Assert\GreaterThan(value=0, message="The price should be greater than 0.")
     * @Assert\Range(
     *      max = 99999,
     *      maxMessage = "The price cannot exceed {{ limit }}."
     * )
     * @ORM\Column(name="price", type="integer", nullable=false)
     */
    private $price;

      /**
     * @var string
     *
     * @ORM\Column(name="ticket_type", type="string", length=50, nullable=false)
     * @Assert\Choice(choices={"Student", "Teen", "Adult"}, message="Invalid ticket type. Allowed values: Student, type2, type3")
     */
    private $ticketType;

    /**
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     *
     * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    
    public function getTicketId(): ?int
    {
        return $this->ticketId;
    }

    public function getTicketDate(): ?\DateTimeInterface
    {
        return $this->ticketDate;
    }

    public function setTicketDate(?\DateTimeInterface $ticketDate): self
    {
        $this->ticketDate = $ticketDate;

        return $this;
    }

    public function getTicketEdate(): ?\DateTimeInterface
    {
        return $this->ticketEdate;
    }

    public function setTicketEdate(?\DateTimeInterface $ticketEdate): self
    {
        $this->ticketEdate = $ticketEdate;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTicketType(): ?string
    {
        return $this->ticketType;
    }

    public function setTicketType(string $ticketType): self
    {
        $this->ticketType = $ticketType;

        return $this;
    }


}
