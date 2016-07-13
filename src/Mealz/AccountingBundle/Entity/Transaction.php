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
     * @ORM\Column(name="id", length=128)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @var string
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="Mealz\UserBundle\Entity\Profile")
     * @ORM\ManyToOne(targetEntity="Mealz\UserBundle\Entity\Profile", inversedBy="transactions")
     * @ORM\JoinColumn(name="profile", referencedColumnName="id", nullable=FALSE)
     * @var Profile
     */
    private $profile;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @var \DateTime
     */
    private $date;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="decimal", precision=10, scale=4, nullable=FALSE)
     * @var float
     */
    private $amount;

    /**
     * @ORM\Column(type="boolean", nullable=FALSE, options={"default": false})
     * @var boolean
     */
    protected $successful = false;

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param integer $amount
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
     * @param \Mealz\UserBundle\Entity\Profile $profile
     *
     * @return Transaction
     */
    public function setProfile(\Mealz\UserBundle\Entity\Profile $profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return \Mealz\UserBundle\Entity\Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param bool $successful
     */
    public function setSuccessful($successful)
    {
        $this->successful = $successful;
    }

    /**
     * @return bool
     */
    public function getSuccessful()
    {
        return $this->successful;
    }
}
