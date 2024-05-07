<?php

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass]
abstract class AbstractMessage
{
    #[Assert\NotNull]
    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $enabled = true;

    #[Assert\Length(min: 8, max: 255)]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $message;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $disabled): void
    {
        $this->enabled = $disabled;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
