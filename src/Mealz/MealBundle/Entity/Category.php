<?php

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="Category")
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
 */
class Category implements JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Slug(fields={"title_en"})
     * @ORM\Column(length=128, unique=true)
     */
    private ?string $slug = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @ORM\Column(type="string", length=255, nullable=FALSE)
     *
     * @var string
     */
    protected $title_en;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @ORM\Column(type="string", length=255, nullable=FALSE)
     *
     * @var string
     */
    protected $title_de;

    protected string $currentLocale = 'en';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return string
     */
    public function getTitleEn()
    {
        return $this->title_en;
    }

    public function setTitleEn(string $title): void
    {
        $this->title_en = $title;
    }

    /**
     * @return string
     */
    public function getTitleDe()
    {
        return $this->title_de;
    }

    public function setTitleDe(string $title): void
    {
        $this->title_de = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
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
     * @return string
     */
    public function getCurrentLocale()
    {
        return $this->currentLocale;
    }

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
