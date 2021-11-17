<?php

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
abstract class AbstractMessage
{
    /**
     * @Assert\NotNull()
     * @ORM\Column(type="boolean", nullable=FALSE)
     * @var bool
     */
    private bool $enabled = true;

    /**
     * @Assert\Length(min=8, max=255)
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     * @var string
     */
    private string $message;

    /**
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param boolean $disabled
     */
    public function setEnabled(bool $disabled): void
    {
        $this->enabled = $disabled;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
