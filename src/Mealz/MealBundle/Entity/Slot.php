<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Defines a meal slot.
 *
 * @ORM\Entity
 * @ORM\Table(name="slot")
 */
class Slot
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id = 0;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\Length(min=5, max=80)
     */
    private string $title = '';

    /**
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    private int $limit = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $disabled = false;


    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function isEnabled(): bool
    {
        return (false === $this->disabled);
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }
}
