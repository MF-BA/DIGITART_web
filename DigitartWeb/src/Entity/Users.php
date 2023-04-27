<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints as Recaptcha;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UsersRepository::class)
 */
class Users implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
     /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null
     * 
     * @ORM\Column(name="cin", type="integer", nullable=true)
     * @Assert\NotBlank(message="Please enter a cin")
     * @Assert\Length(
     *      min = 8,
     *      max = 8,
     *      exactMessage = "Cin must contain exactly {{ limit }} digits"
     * )
     * @Assert\Regex(
     *      pattern = "/^\d+$/",
     *      message = "Cin must be a number"
     * )
     */
    private $cin;

    /**
     * @var string|null
     * 
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Please enter a first name")
     * @Assert\Regex(
     *      pattern = "/^[a-zA-Z ]+$/",
     *      message = "Please enter a valid first name"
     * )
     */
    private $firstname;

     /**
     * @var string|null
     * 
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Please enter a last name")
     * @Assert\Regex(
     *      pattern = "/^[a-zA-Z ]+$/",
     *      message = "Please enter a valid last name"
     * )
     */
    private $lastname;

    /**
     * @var string|null
     * 
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Please enter an email")
     * @Assert\Email(message="Please enter a valid email address")
     */
    private $email;

    /**
     * @var string|null
     * 
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     * @Assert\Length(
     *      min = 6,
     *      max = 4096,
     *      minMessage = "Your password should be at least {{ limit }} characters",
     *      maxMessage = "Your password is too long"
     * )
     * @Assert\Regex(
     *      pattern = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#\$%\^&\*\(\)]).+$/",
     *      message = "Password should contain at least one uppercase letter, one lowercase letter, and one special character (!@#$%^&*())"
     * )
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Please enter an address")
     */
    private $address;

    /**
     * @var int|null
     * 
     * @ORM\Column(name="phone_num", type="integer", nullable=true)
     * @Assert\NotBlank(message="Please enter a phone number")
     * @Assert\Length(
     *      min = 8,
     *      max = 8,
     *      exactMessage = "Phone number must contain exactly {{ limit }} digits"
     * )
     * @Assert\Regex(
     *      pattern = "/^\d+$/",
     *      message = "Phone number must be a number"
     * )
     */
    private $phoneNum;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="birth_date", type="date", nullable=true)
     * @Assert\NotBlank(message="Please enter a birth date")
     */
    private $birthDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="gender", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Please select a gender")
     */
    private $gender;

    /**
     * @var string|null
     *
     * @ORM\Column(name="role", type="string", length=255, nullable=true, options={"default"="Subscriber"})
     * @Assert\NotBlank(message="Please select a role")
     */
    private $role = 'Subscriber';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=false, options={"default"="unblocked"})
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string|null
     *
     * @ORM\Column(name="secretcode", type="string", length=255, nullable=true)
     */
    private $secretcode;

    /**
    * @ORM\Column(type="json", nullable=true)
    */
    private array $roles = [];

     /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserImages", mappedBy="users", orphanRemoval=true, cascade={"persist"})
     */
    private $userImages;

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
     * @ORM\Column(type="boolean")
     */
    private $is_verified = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $authCode;


    /**
     * @var string|null
     *
     * @ORM\Column(name="resetToken", type="string", length=100, nullable=true)
     */
    private $resetToken;
 
    /**
    * @Recaptcha\IsTrueV3
    */
    
    public $recaptcha;

    protected $captchaCode;
    
    public function getCaptchaCode()
    {
      return $this->captchaCode;
    }

    public function setCaptchaCode($captchaCode)
    {
      $this->captchaCode = $captchaCode;
    }

    public function __construct()
    {
        $this->userImages = new ArrayCollection();
        $this->status = 'unblocked';
    }

    public function getIsVerified(): ?bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(bool $is_verified): self
    {
        $this->is_verified = $is_verified;

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

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    /**
     * @return Collection|Images[]
     */
    public function getUserImages(): Collection
    {
        return $this->userImages;
    }

    public function addUserImage(UserImages $userImage): self
    {
        if (!$this->userImages->contains($userImage)) {
            $this->userImages->add($userImage);
            $userImage->setusers($this);
        }

        return $this;
    }

    public function removeUserImage(UserImages $userImage): self
    {
        if ($this->userImages->removeElement($userImage)) {
            // set the owning side to null (unless already changed)
            if ($userImage->getusers() === $this) {
                $userImage->setusers(null);
            }
        }

        return $this;
    }
     
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCin(): ?int
    {
        return $this->cin;
    }

    public function setCin(?int $cin): self
    {
        $this->cin = $cin;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

   
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }


    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPhoneNum(): ?int
    {
        return $this->phoneNum;
    }

    public function setPhoneNum(?int $phoneNum): self
    {
        $this->phoneNum = $phoneNum;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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

    public function getSecretcode(): ?string
    {
        return $this->secretcode;
    }

    public function setSecretcode(?string $secretcode): self
    {
        $this->secretcode = $secretcode;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
 
    public function isEmailAuthEnabled(): bool
    { 
        return true;
    }
    
    public function getEmailAuthRecipient(): string
    {
     return $this->email;
    }
    
    public function getEmailAuthCode(): ?string
    {
    if(null === $this->authCode){
        throw new \LogicException('the email authentication code was not set');
     }

     return $this->authCode;
    }
    
    public function setEmailAuthCode(string $authCode): void
    {
    $this->authCode= $authCode;
    }

    
}
