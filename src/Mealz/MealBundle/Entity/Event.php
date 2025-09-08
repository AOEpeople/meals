<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use Override;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Defines an event.
 */
#[ORM\Entity]
#[ORM\Table(name: 'event')]
class Event implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id = 0;

    #[Assert\Length(min: 5, max: 80)]
    #[ORM\Column(type: 'string', nullable: false)]
    private string $title = '';

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $deleted = false;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(type: 'string', length: 128, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
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

    /**
     * @return (bool|int|string|null)[]
     *
     * @psalm-return array{id: int, title: string, slug: null|string, public: bool}
     */
    #[Override]
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
