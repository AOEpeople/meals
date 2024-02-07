<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Defines a event.
 *
 * @ORM\Entity
 * @ORM\Table(name="event")
 */
class Event implements JsonSerializable
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
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $deleted = false;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=128, unique=true)
     */
    private ?string $slug = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $public = false;

    public function __construct(string $title = '', bool $isPublic = false)
    {
        $this->title = $title;
        $this->public = $isPublic;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): bool
    {
        return $this->deleted = $deleted;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): bool
    {
        return $this->public = $public;
    }

    public function getSlug(): string
    {
        return $this->slug ?? '';
    }

    public function setSlug(string $slug): string
    {
        return $this->slug = $slug;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'public' => $this->public,
        ];
    }
}
