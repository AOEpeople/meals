<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests;

use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractDatabaseTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
    }

    /**
     * empty the test database and load fixtures from a class.
     *
     * @param FixtureInterface[]|null $fixtures
     *
     * @throws InvalidArgumentException
     */
    protected function loadFixtures(?array $fixtures = null): void
    {
        $loader = new Loader();

        if (null === $fixtures) {
            $this->push($loader);

            return;
        }

        foreach ($fixtures as $fixture) {
            if (!($fixture instanceof FixtureInterface)) {
                throw new InvalidArgumentException(sprintf('Expected "%s", got "%s".', FixtureInterface::class, gettype($fixture)));
            }

            $loader->addFixture($fixture);
        }

        $this->push($loader);
    }

    protected function clearAllTables(): void
    {
        $this->loadFixtures(null);
    }

    protected function getDoctrine(): Registry
    {
        return static::$kernel->getContainer()->get('doctrine');
    }

    protected function createDishVariation(?Dish $parent = null, ?Category $category = null): DishVariation
    {
        $dishVariation = new DishVariation();
        $dishVariation->setParent($parent ?? $this->createDish());

        $dishVariation->setTitleEn('Test EN ' . mt_rand());
        $dishVariation->setTitleDe('Test DE ' . mt_rand());
        $dishVariation->setPrice(3.20);
        if ($category) {
            $dishVariation->setCategory($category);
        }

        return $dishVariation;
    }

    protected function createDish(?Category $category = null): Dish
    {
        $dish = new Dish();
        $dish->setTitleEn('Test EN ' . mt_rand());
        $dish->setTitleDe('Test DE ' . mt_rand());
        $dish->setPrice(3.20);
        if ($category) {
            $dish->setCategory($category);
        }

        return $dish;
    }

    protected function createMeal(?Dish $dish = null, ?Day $day = null): Meal
    {
        $dish = $dish ?: $this->createDish();
        $day = $day ?: new Day();

        $meal = new Meal($dish, $day);
        $meal->setPrice(1.23);

        return $meal;
    }

    protected function createEvent(): Event
    {
        $event = new Event();
        $event->setTitle('TestEvent' . mt_rand());

        return $event;
    }

    protected function createProfile(): Profile
    {
        $rand = mt_rand();
        $profile = new Profile();
        $profile->setUsername('TestUsername' . $rand);
        $profile->setName('TestName' . $rand);
        $profile->setFirstName('TestFirstName' . $rand);

        return $profile;
    }

    protected function createCategory(): Category
    {
        $uniqueSuffix = mt_rand();
        $category = new Category();
        $category->setTitleDe('Title DE ' . $uniqueSuffix);
        $category->setTitleEn('Title EN ' . $uniqueSuffix);

        return $category;
    }

    public function persistAndFlushAll(array $entities): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->wrapInTransaction(
            static function (EntityManagerInterface $entityManager) use ($entities) {
                // transaction is need for Participant entities
                foreach ($entities as $entity) {
                    if ($entity instanceof Meal) {
                        $entityManager->persist($entity->getDish());
                        $entityManager->persist($entity->getDay()->getWeek());
                        $entityManager->persist($entity->getDay());
                    }

                    $entityManager->persist($entity);
                }

                $entityManager->flush();
            }
        );
    }

    protected function push(Loader $loader): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $purger = new ORMPurger($entityManager);
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
