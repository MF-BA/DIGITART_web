<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
/**
 * Payment
 *
 * @ORM\Table(name="payment", indexes={@ORM\Index(name="pk", columns={"user_id"})})
 * @ORM\Entity
 */
class Payment
{
    /**
     * @var int
     *
     * @ORM\Column(name="payment_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups("payments")
     */
    private $paymentId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="purchase_date", type="date", nullable=false)
     * @Groups("payments")
     */
    private $purchaseDate;

    /**
     * @var int|null
     *
     * @ORM\Column(name="nb_adult", type="integer", nullable=true)
     * @Groups("payments")
     */
    private $nbAdult;

    /**
     * @var int|null
     *
     * @ORM\Column(name="nb_teenager", type="integer", nullable=true)
     * @Groups("payments")
     */
    private $nbTeenager;

    /**
     * @var int|null
     *
     * @ORM\Column(name="nb_student", type="integer", nullable=true)
     * @Groups("payments")
     */
    private $nbStudent;

    /**
     * @var int|null
     *
     * @ORM\Column(name="total_payment", type="integer", nullable=false)
     * @Groups("payments")
     */
    private $totalPayment;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="paid", type="boolean", nullable=true)
     * @Groups("payments")
     */
    private $paid;

    /**
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     * 
     */
    private $createdAt;

    /**
     *
     * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     * 
     */
    private $updatedAt;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     * @Groups("payments")
     */
    private $user;

    public function getPaymentId(): ?int
    {
        return $this->paymentId;
    }

    public function setPaymentId(?int $paymentId): ?self
    {
        $this->paymentId=$paymentId;
        return $this;
    }

   

    public function getPurchaseDate(): ?\DateTimeInterface
    {
        return $this->purchaseDate;
    }

    public function setPurchaseDate(\DateTimeInterface $purchaseDate): self
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    public function getNbAdult(): ?int
    {
        return $this->nbAdult;
    }

    public function setNbAdult(?int $nbAdult): self
    {
        $this->nbAdult = $nbAdult;

        return $this;
    }

    public function getNbTeenager(): ?int
    {
        return $this->nbTeenager;
    }

    public function setNbTeenager(?int $nbTeenager): self
    {
        $this->nbTeenager = $nbTeenager;

        return $this;
    }

    public function getNbStudent(): ?int
    {
        return $this->nbStudent;
    }

    public function setNbStudent(?int $nbStudent): self
    {
        $this->nbStudent = $nbStudent;

        return $this;
    }

    public function getTotalPayment(): ?int
    {
        return $this->totalPayment;
    }

    public function setTotalPayment(?int $totalPayment): self
    {
        $this->totalPayment = $totalPayment;

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(?bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
    $this->createdAt = $createdAt;

    return $this;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
    $this->updatedAt = $updatedAt;

    return $this;
    }

}
