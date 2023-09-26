<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Defines a event.
 *
 * @ORM\Entity
 * @ORM\Table(name="event")
 */
class Event
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
     * @ORM\OneToMany(targetEntity="App\Mealz\MealBundle\Entity\Participant", mappedBy="event")
     */
    private ?Collection $participants = null;

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

    public function getSlug(): string
    {
        return $this->slug ?? '';
    }

    public function setSlug(string $slug): string
    {
        return $this->slug = $slug;
    }

    public function getParticipants(): ArrayCollection
    {
        if (null === $this->participants) {
            $this->participants = new ArrayCollection();
        }

        return new ArrayCollection($this->participants->toArray());
    }
}
