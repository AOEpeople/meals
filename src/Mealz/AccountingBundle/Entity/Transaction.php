<?php

namespace App\Mealz\AccountingBundle\Entity;

use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="transaction")
 * @ORM\Entity(repositoryClass="TransactionRepository")
 */
class Transaction
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Profile
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="App\Mealz\UserBundle\Entity\Profile")
     * @ORM\ManyToOne(targetEntity="App\Mealz\UserBundle\Entity\Profile", cascade={"persist"})
     * @ORM\JoinColumn(name="profile", referencedColumnName="id", nullable=FALSE)
     */
    private $profile;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $date;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="decimal", precision=10, scale=4, nullable=FALSE)
     */
    private $amount;

    /**
     * @Assert\Length(min=3, max=2048)
     * @ORM\Column(type="string", length=2048, nullable=TRUE)
     *
     * @var string
     */
    private $paymethod;

    /**
     * @ORM\Column(type="string", length=24, unique=TRUE, nullable=TRUE)
     */
    private ?string $orderId = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param float $amount
     *
     * @return Transaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * @param string $paymethod
     *
     * @return Transaction
     */
    public function setPaymethod($paymethod)
    {
        $this->paymethod = $paymethod;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymethod()
    {
        return $this->paymethod;
    }

    /**
     * @return Transaction
     */
    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    public function __toString()
    {
        return $this->profile . ' ' . $this->amount;
    }
}
