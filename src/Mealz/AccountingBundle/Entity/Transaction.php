<?php

namespace Mealz\AccountingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Transaction
 *
 * @ORM\Table(name="transaction")
 * @ORM\Entity(repositoryClass="TransactionRepository")
 */
class Transaction
{
    /**
     * @var integer
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
     * @Assert\Type(type="Mealz\UserBundle\Entity\Profile")
     * @ORM\ManyToOne(targetEntity="Mealz\UserBundle\Entity\Profile", inversedBy="transactions", cascade="persist")
     * @ORM\JoinColumn(name="profile", referencedColumnName="id", nullable=FALSE)
     */
    private $profile;

    /**
     * @var \DateTime
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
     * @var string
     */
    private $paymethod;

    /**
     * @Assert\Length(min=3, max=2048)
     * @ORM\Column(type="string", length=2048, nullable=TRUE)
     * @var string
     */
    private $orderId;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return \DateTime
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

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param integer $orderId
     *
     * @return Transaction
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderId()
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
     * @param Profile $profile
     *
     * @return Transaction
     */
    public function setProfile(\Mealz\UserBundle\Entity\Profile $profile)
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
