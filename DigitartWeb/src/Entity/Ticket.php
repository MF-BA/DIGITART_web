<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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
     *
     * @ORM\Column(name="ticket_date", type="date", nullable=true)
     */
    private $ticketDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="ticket_edate", type="date", nullable=true)
     */
    private $ticketEdate;

    /**
     * @var int
     *
     * @ORM\Column(name="price", type="integer", nullable=false)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="ticket_type", type="string", length=50, nullable=false)
     */
    private $ticketType;

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
