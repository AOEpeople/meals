<?php

namespace App\Mealz\MealBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="dish")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"dish" = "Dish", "dish_variation" = "DishVariation"})
 * @ORM\HasLifecycleCallbacks()
 */
class Dish implements JsonSerializable
{
    public const COMBINED_DISH_SLUG = 'combined-dish';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @TODO: CHECK IF THIS WORKS. Add 'title_de' to the update field list 'fields={"title_en"}', check with Jonathan
     * @Gedmo\Slug(handlers={
     *   @Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\InversedRelativeSlugHandler", options={
     *       @Gedmo\SlugHandlerOption(name="relationClass", value="App\Mealz\MealBundle\Entity\Dish"),
     *       @Gedmo\SlugHandlerOption(name="mappedBy", value="parent"),
     *       @Gedmo\SlugHandlerOption(name="inverseSlugField", value="slug")
     *      })
     *   }, fields={"title_en"})
     * @ORM\Column(length=128, unique=true)
     *
     * @var string
     */
    protected $slug;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @ORM\Column(type="string", length=255, nullable=FALSE)
     */
    protected string $title_en = 'New Dish';

    /**
     * @Assert\Length(max=4096)
     * @ORM\Column(type="text", nullable=TRUE)
     */
    protected ?string $description_en = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @ORM\Column(type="string", length=255, nullable=FALSE)
     */
    protected string $title_de = 'Neues Gericht';

    /**
     * @Assert\Length(max=4096)
     * @ORM\Column(type="text", nullable=TRUE)
     */
    protected ?string $description_de = null;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="dishes")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected ?Category $category = null;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="decimal", precision=10, scale=4, nullable=FALSE)
     */
    protected float $price = 0.0;

    /**
     * @ORM\Column(type="boolean", nullable=FALSE)
     */
    protected bool $enabled = true;

    /**
     * Dish with this flag set can only have obe serving size.
     *
     * It can not be reduced, like half a portion or so.
     *
     * @ORM\Column(name="one_serving_size", type="boolean", nullable=FALSE)
     */
    protected bool $oneServingSize = false;

    protected string $currentLocale = 'en';

    /**
     * @ORM\OneToMany(targetEntity="DishVariation", mappedBy="parent", cascade={"persist"})
     */
    protected ?Collection $variations = null;

    /**
     * Parent property references to the same table dish.
     * If an dish, which is referenced by an dish_variation, is deleted the related dish_variations are deleted cascadingly.
     *
     * @ORM\ManyToOne(targetEntity="Dish", inversedBy="variations", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=TRUE, onDelete="CASCADE")
     */
    protected ?Dish $parent = null;

    public function getParent(): ?Dish
    {
        return $this->parent;
    }

    public function setParent(?Dish $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        if ('de' === $this->currentLocale && $this->description_de) {
            return $this->getDescriptionDe();
        }

        return $this->getDescriptionEn();
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getTitle(): string
    {
        if ('de' === $this->currentLocale && $this->title_de) {
            return $this->getTitleDe();
        }

        return $this->getTitleEn();
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setOneServingSize(bool $oneServingSize): void
    {
        $this->oneServingSize = $oneServingSize;
    }

    public function hasOneServingSize(): bool
    {
        return $this->oneServingSize;
    }

    public function setCurrentLocale(string $currentLocale): void
    {
        $this->currentLocale = $currentLocale;
    }

    public function getCurrentLocale(): string
    {
        return $this->currentLocale;
    }

    public function setDescriptionDe(?string $description_de): void
    {
        $this->description_de = $description_de;
    }

    public function getDescriptionDe(): ?string
    {
        return $this->description_de;
    }

    public function setDescriptionEn(?string $description_en): void
    {
        $this->description_en = $description_en;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->description_en;
    }

    public function setTitleDe(string $title_de): void
    {
        $this->title_de = $title_de;
    }

    public function getTitleDe(): string
    {
        return $this->title_de;
    }

    public function setTitleEn(string $title_en): void
    {
        $this->title_en = $title_en;
    }

    public function getTitleEn(): string
    {
        return $this->title_en;
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    public function getVariations(): DishCollection
    {
        if (null === $this->variations) {
            $this->variations = new DishCollection();
        }

        return new DishCollection($this->variations->toArray());
    }

    public function setVariations(Collection $dishVariations): void
    {
        $this->variations = $dishVariations;
    }

    public function hasVariations(): bool
    {
        return null !== $this->variations && count($this->variations) > 0;
    }

    public function isCombinedDish(): bool
    {
        return self::COMBINED_DISH_SLUG === $this->slug;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'titleDe' => $this->title_de,
            'titleEn' => $this->title_en,
            'descriptionDe' => $this->description_de,
            'descriptionEn' => $this->description_en,
            'categoryId' => null !== $this->category ? $this->category->getId() : null,
            'oneServingSize' => $this->oneServingSize,
            'parentId' => null !== $this->parent ? $this->parent->getId() : null,
            'variations' => $this->hasVariations() ? $this->variations->toArray() : [],
        ];
    }
}
