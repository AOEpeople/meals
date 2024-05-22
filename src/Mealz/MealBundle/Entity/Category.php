<?php

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
 */
#[ORM\Entity]
#[ORM\Table(name: 'Category')]
class Category implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[Gedmo\Slug(fields: ['title_en'])]
    #[ORM\Column(type: 'string', length: 128, unique: true)]
    private ?string $slug = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $title_en;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $title_de;

    private string $currentLocale = 'en';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug ?? '';
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getTitleEn(): string
    {
        return $this->title_en;
    }

    public function setTitleEn(string $title): void
    {
        $this->title_en = $title;
    }

    public function getTitleDe(): string
    {
        return $this->title_de;
    }

    public function setTitleDe(string $title): void
    {
        $this->title_de = $title;
    }

    public function getTitle(): string
    {
        if ('de' === $this->currentLocale && $this->title_de) {
            return $this->getTitleDe();
        }

        return $this->getTitleEn();
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function setCurrentLocale(string $locale): void
    {
        $this->currentLocale = $locale;
    }

    /**
     * @return (int|null|string)[]
     *
     * @psalm-return array{id: int|null, titleDe: string, titleEn: string, slug: null|string}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'titleDe' => $this->title_de,
            'titleEn' => $this->title_en,
            'slug' => $this->slug,
        ];
    }
}
