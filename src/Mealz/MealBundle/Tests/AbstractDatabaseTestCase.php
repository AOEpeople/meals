<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractDatabaseTestCase extends WebTestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
    }

    /**
     * empty the test database and load fixtures from a class
     *
     * @param FixtureInterface|array|null $fixtures
     *
     * @throws InvalidArgumentException
     */
    protected function loadFixtures($fixtures): void
    {
        $loader = new Loader();

        if (is_array($fixtures) || $fixtures instanceof \Iterator) {
            foreach ($fixtures as $fixture) {
                $loader->addFixture($fixture);
            }
            $this->push($loader);

            return;
        }

        if ($fixtures instanceof FixtureInterface) {
            $loader->addFixture($fixtures);
            $this->push($loader);

            return;
        }

        if ($fixtures === null) {
            $this->push($loader);
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                '%s expects first parameter to be a FixtureInterface or array. %s given.',
                __METHOD__,
                get_class($fixtures)
            )
        );
    }

    protected function clearAllTables(): void
    {
        $this->loadFixtures(null);
    }

    /**
     * @return Registry
     */
    protected function getDoctrine()
    {
        return static::$kernel->getContainer()->get('doctrine');
    }

    protected function createDish(Category $category = null): Dish
    {
        $dish = new Dish();
        $dish->setTitleEn('Test EN '.mt_rand());
        $dish->setTitleDe('Test DE '.mt_rand());
        $dish->setPrice(3.20);
        if ($category) {
            $dish->setCategory($category);
        }

        return $dish;
    }

    protected function createMeal(Dish $dish = null, DateTime $datetime = null): Meal
    {
        if ($datetime === null) {
            $datetime = new DateTime();
            $datetime->setTime(12, 0);
        }

        $meal = new Meal();
        $meal->setDish($dish ?: $this->createDish());
        $meal->setDateTime($datetime);
        $meal->setPrice(1.23);

        return $meal;
    }

    protected function createProfile(): Profile
    {
        $rand = mt_rand();
        $profile = new Profile();
        $profile->setUsername('TestUsername'.$rand);
        $profile->setName('TestName'.$rand);
        $profile->setFirstName('TestFirstName'.$rand);

        return $profile;
    }

    protected function createCategory(): Category
    {
        $uniqSuffix = mt_rand();
        $category = new Category();
        $category->setTitleDe('Title DE '.$uniqSuffix);
        $category->setTitleEn('Title EN '.$uniqSuffix);

        return $category;
    }

    public function persistAndFlushAll(array $entities): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->transactional(
            static function (EntityManagerInterface $entityManager) use ($entities) {
                // transaction is need for Participant entities
                foreach ($entities as $entity) {
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
