<?php


namespace App\Mealz\MealBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\UserBundle\Entity\Login;
use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AbstractDatabaseTestCase
 * @package Mealz\MealBundle\Tests
 */
abstract class AbstractDatabaseTestCase extends WebTestCase
{

    /**
     * set up
     */
    protected function setUp(): void
    {
        parent::setUp();
        static::$kernel = static::createKernel(['debug' => false]);
        static::$kernel->boot();
    }

    /**
     * empty the test database and load fixtures from a class
     *
     * @param FixtureInterface|array|null $fixtures
     * @throws \InvalidArgumentException
     */
    protected function loadFixtures($fixtures)
    {
        $loader = new Loader();
        if (is_array($fixtures) || $fixtures instanceof \Iterator) {
            foreach ($fixtures as $fixture) {
                $loader->addFixture($fixture);
            }
            $this->push($loader);
            return;
        } elseif ($fixtures instanceof FixtureInterface) {
            $loader->addFixture($fixtures);
            $this->push($loader);
            return;
        } elseif ($fixtures === null) {
            $this->push($loader);
            return;
        }

        throw new \InvalidArgumentException(
            sprintf(
                '%s expects first parameter to be a FixtureInterface or array. %s given.',
                __METHOD__,
                get_class($fixtures)
            )
        );
    }

    protected function clearAllTables()
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

    /**
     * @param Category $category
     * @return Dish
     */
    protected function createDish(Category $category = null)
    {
        $dish = new Dish();
        $dish->setTitleEn('Test EN '.rand());
        $dish->setTitleDe('Test DE '.rand());
        $dish->setPrice(3.20);
        if ($category) {
            $dish->setCategory($category);
        }

        return $dish;
    }

    /**
     * @param \App\Mealz\MealBundle\Entity\Dish $dish
     * @param $datetime
     * @return Meal
     */
    protected function createMeal(Dish $dish = null, $datetime = null)
    {
        if ($datetime === null) {
            $datetime = new \DateTime();
        }
        $meal = new Meal();
        $meal->setDish($dish ?: $this->createDish());
        $meal->setDateTime($datetime);
        $meal->setPrice(1.23);

        return $meal;
    }

    /**
     * @return Profile
     */
    protected function createProfile()
    {
        $rand = rand();
        $profile = new Profile();
        $profile->setUsername('TestUsername'.$rand);
        $profile->setName('TestName'.$rand);
        $profile->setFirstName('TestFirstName'.$rand);

        return $profile;
    }

    protected function createLogin(Profile $profile = null)
    {
        $name = $profile ? $profile->getName() : rand();
        $login = new Login();
        $login->setUsername($name);
        $login->setSalt(md5(uniqid(null, true)));

        /** @var Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface $encoder */
        $encoder = static::$kernel->getContainer()->get('security.encoder_factory')->getEncoder($login);
        $login->setPassword($encoder->encodePassword($name, $login->getSalt()));

        if ($profile) {
            $login->setProfile($profile);
        }

        return $login;
    }

    /**
     * @return Category
     */
    protected function createCategory()
    {
        $category = new Category();
        $category->setTitleDe('Title DE '.rand());
        $category->setTitleEn('Title EN '.rand());

        return $category;
    }

    /**
     * @param array $entities
     */
    public function persistAndFlushAll($entities)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->transactional(
            function ($entityManager) use ($entities) {
                /** @var EntityManager $entityManager */
                // transaction is need for Participant entities
                foreach ($entities as $entity) {
                    $entityManager->persist($entity);
                }
                $entityManager->flush();
            }
        );
    }

    /**
     * @param Loader $loader
     */
    protected function push($loader)
    {
        $entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $purger = new ORMPurger($entityManager);
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
