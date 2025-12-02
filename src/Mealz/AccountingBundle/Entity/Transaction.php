<?php

namespace App\Mealz\AccountingBundle\Entity;

use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Override;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'transaction')]
class Transaction implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: Profile::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'profile', referencedColumnName: 'id', nullable: false)]
    private Profile $profile;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private DateTime $date;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'decimal', precision: 10, scale: 4, nullable: false)]
    private string $amount;

    #[Assert\Length(min: 3, max: 2048)]
    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    private ?string $paymethod;

    #[ORM\Column(type: 'string', length: 24, unique: true, nullable: true)]
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

    public function setAmount(float $amount): static
    {
        $this->amount = (string) $amount;

        return $this;
    }

    public function getAmount(): float
    {
        return (float) $this->amount;
    }

    public function setOrderId(string $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function setPaymethod(string $paymethod): static
    {
        $this->paymethod = $paymethod;

        return $this;
    }

    public function getPaymethod(): ?string
    {
        return $this->paymethod;
    }

    public function setProfile(Profile $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }

    #[Override]
    public function __toString()
    {
        return $this->profile . ' ' . $this->amount;
    }
}
