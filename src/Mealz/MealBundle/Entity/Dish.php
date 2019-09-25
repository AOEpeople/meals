<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Dish
 *
 * @ORM\Table(name="dish")
 * @ORM\Entity(repositoryClass="DishRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"dish" = "Dish", "dish_variation" = "DishVariation"})
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Dish
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @TODO: CHECK IF THIS WORKS. Add 'title_de' to the update field list 'fields={"title_en"}', check with Jonathan
     * @Gedmo\Slug(handlers={
     *   @Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\InversedRelativeSlugHandler", options={
     *       @Gedmo\SlugHandlerOption(name="relationClass", value="Mealz\MealBundle\Entity\Dish"),
     *       @Gedmo\SlugHandlerOption(name="mappedBy", value="parent"),
     *       @Gedmo\SlugHandlerOption(name="inverseSlugField", value="slug")
     *      })
     *   }, fields={"title_en"})
     * @ORM\Column(length=128, unique=true)
     * @var string
     */
    protected $slug;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @ORM\Column(type="string", length=255, nullable=FALSE)
     * @var string
     */
    protected $title_en;

    /**
     * @Assert\Length(max=4096)
     * @ORM\Column(type="text", nullable=TRUE)
     * @var null|string
     */
    protected $description_en = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @ORM\Column(type="string", length=255, nullable=FALSE)
     * @var string
     */
    protected $title_de;

    /**
     * @Assert\Length(max=4096)
     * @ORM\Column(type="text", nullable=TRUE)
     * @var null|string
     */
    protected $description_de = null;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="dishes")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="SET NULL")
     * @var null|Category
     */
    protected $category = null;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="decimal", precision=10, scale=4, nullable=FALSE)
     * @var float
     */
    protected $price;

    /**
     * @ORM\Column(type="boolean", nullable=FALSE)
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var string
     */
    protected $currentLocale = 'en';

    /**
     * @ORM\OneToMany(targetEntity="DishVariation", mappedBy="parent")
     * @var Collection
     */
    protected $variations;

    /**
     * Parent property references to the same table dish.
     * If an dish, which is referenced by an dish_variation, is deleted the related dish_variations are deleted cascadingly.
     *
     *
     * @ORM\ManyToOne(targetEntity="Dish", inversedBy="variations", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=TRUE, onDelete="CASCADE")
     * @var Dish
     */
    protected $parent = null;

    /**
     * The entityManager of the class
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Holds isNew FLag without storing in Database. Default true
     * @var bool
     */
    protected $isNew = true;

    /**
     * Needed to get EntityManager
     *
     * @ORM\PostLoad @ORM\PostPersist
     *
     * @param \Doctrine\Common\Persistence\Event\LifecycleEventArgs $args The arguments
     */
    public function fetchEntityManager(LifecycleEventArgs $args)
    {
        $this->entityManager = ($args->getEntityManager());
    }

    /**
     * @return Dish
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Dish $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get id
     *
     * @return integer
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

    /**
     * @deprecated use setDescriptionEn() instead
     * @param null|string $description
     */
    public function setDescription($description)
    {
        $this->setDescriptionEn($description);
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        if ($this->currentLocale == 'de' && $this->description_de) {
            return $this->getDescriptionDe();
        } else {
            return $this->getDescriptionEn();
        }
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $title
     * @deprecated use setTitleEn() or setTitleDe() instead
     */
    public function setTitle($title)
    {
        $this->setTitleEn($title);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if ($this->currentLocale == 'de' && $this->title_de) {
            return $this->getTitleDe();
        } else {
            return $this->getTitleEn();
        }
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param string $currentLocale
     */
    public function setCurrentLocale($currentLocale)
    {
        $this->currentLocale = $currentLocale;
    }

    /**
     * @return string
     */
    public function getCurrentLocale()
    {
        return $this->currentLocale;
    }

    /**
     * @param null|string $description_de
     */
    public function setDescriptionDe($description_de)
    {
        $this->description_de = $description_de;
    }

    /**
     * @return null|string
     */
    public function getDescriptionDe()
    {
        return $this->description_de;
    }

    /**
     * @param null|string $description_en
     */
    public function setDescriptionEn($description_en)
    {
        $this->description_en = $description_en;
    }

    /**
     * @return null|string
     */
    public function getDescriptionEn()
    {
        return $this->description_en;
    }

    /**
     * @param string $title_de
     */
    public function setTitleDe($title_de)
    {
        $this->title_de = $title_de;
    }

    /**
     * @return string
     */
    public function getTitleDe()
    {
        return $this->title_de;
    }

    /**
     * @param string $title_en
     */
    public function setTitleEn($title_en)
    {
        $this->title_en = $title_en;
    }

    /**
     * @return string
     */
    public function getTitleEn()
    {
        return $this->title_en;
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param null|Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * Gets all the dish variations.
     *
     * @return Collection
     */
    public function getVariations()
    {
        return $this->variations;
    }

    /**
     * @param Collection $dishVariations
     */
    public function setVariations(Collection $dishVariations)
    {
        $this->variations = $dishVariations;
    }

    /**
     * Checks if the dish has variations.
     *
     * @return bool
     */
    public function hasVariations()
    {
        return (count($this->variations) > 0);
    }

    /**
     * Checks if the dish has variations.
     *
     * @return bool
     */
    public function isNew()
    {
        // Only way to get Config-Parameters in an entity
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        $dishRepository = $this->entityManager->getRepository('MealzMealBundle:Dish');

        if ($dishRepository === null || $kernel === null) {
            return $this->isNew;
        }
        $newFlagCounter = $kernel->getContainer()->getParameter('mealz.meal.new_flag_counter');
        $newSearchTimestamp = $kernel->getContainer()->getParameter('mealz.meal.search_timestamp');

        if (is_int($newFlagCounter) === false) {
            $newFlagCounter = 2;
        }

        if ($newSearchTimestamp === null) {
            $newSearchTimestamp = '2000-01-01';
        }

        if ($dishRepository->countNumberDishWasTaken($this, $newSearchTimestamp) >= $newFlagCounter) {
            $this->setIsNew(false);
        }
        return $this->isNew;
    }

    /**
     * Checks if the dish has variations.
     *
     * @return bool
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;
    }
}
