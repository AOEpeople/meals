<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Defines a meal slot.
 */
#[ORM\Entity]
#[ORM\Table(name: 'slot')]
class Slot implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[Assert\Length(min: 5, max: 80)]
    #[ORM\Column(type: 'string', nullable: false)]
    private string $title = '';

    /**
     * Maximum number of people allowed to have their meal in given slot. Zero means no limit.
     */
    #[ORM\Column(name: '`limit`', type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private int $limit = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $disabled = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $deleted = false;

    /**
     * Sort order.
     */
    #[ORM\Column(name: '`order`', type: 'integer', options: ['default' => 0])]
    private int $order = 0;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(type: 'string', length: 128, unique: true)]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'slot', targetEntity: Participant::class)]
    private ?Collection $participants = null;

    public function getId(): ?int
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

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): bool
    {
        return $this->deleted = $deleted;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function isEnabled(): bool
    {
        return false === $this->disabled;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }

    public function getSlug(): string
    {
        return $this->slug ?? '';
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getParticipants(): ArrayCollection
    {
        if (null === $this->participants) {
            $this->participants = new ArrayCollection();
        }

        return new ArrayCollection($this->participants->toArray());
    }

    /**
     * @return (bool|int|string|null)[]
     *
     * @psalm-return array{id: int|null, title: string, limit: int, order: int, enabled: bool, slug: null|string}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'limit' => $this->limit,
            'order' => $this->order,
            'enabled' => $this->isEnabled(),
            'slug' => $this->slug,
        ];
    }
}
