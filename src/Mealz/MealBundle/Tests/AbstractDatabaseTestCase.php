<?php
namespace Mealz\MealBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\Category;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\DishVariation;
use Mealz\MealBundle\Entity\Meal;
use Mealz\UserBundle\Entity\Login;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

abstract class AbstractDatabaseTestCase extends WebTestCase
{

    /**
     * The setUp method for this Test
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }

    /**
     * Empty the test database and load fixtures from a class
     *
     * @param FixtureInterface|array|null $fixtures
     * @throws \InvalidArgumentException
     */
    protected function loadFixtures($fixtures = NULL)
    {
        // TODO: supress output on the commandline during phpunit test run
        //Create Schema, ensure the database tables are existend before they are truncated!!
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application(static::$kernel);
        $application->setAutoExit(false);
        $options = array('command' => 'doctrine:schema:update', "--force" => true);
        $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));

        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $loader = new Loader();
        if (is_array($fixtures) || $fixtures instanceof \Iterator) {
            foreach ($fixtures as $fixture) {
                if ($fixtures instanceof FixtureInterface) {
                    $loader->addFixture($fixture);
                }
            }
        } elseif ($fixtures instanceof FixtureInterface) {
            $loader->addFixture($fixtures);
        } elseif ($fixtures === NULL) {
            // nothing to do
        } else {
            throw new \InvalidArgumentException(sprintf(
                '%s expects first parameter to be a FixtureInterface or array. %s given.',
                __METHOD__,
                get_class($fixtures)
            ));
        }
        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures());
    }


    /**
     * Truncates the database's tables
     * @return void
     */
    protected function clearAllTables()
    {
        $this->loadFixtures();
    }

    /**
     * Return the container's doctrine object
     * @return Registry
     */
    protected function getDoctrine()
    {
        return static::$kernel->getContainer()->get('doctrine');
    }

    /**
     * Create a new dish with random title
     * @param Category $category
     * @return Dish
     */
    protected function createDish(Category $category = null)
    {
        $dish = new Dish();
        $dish->setTitleEn('Test EN ' . rand());
        $dish->setTitleDe('Test DE ' . rand());
        $dish->setPrice(7.45);
        if ($category) {
            $dish->setCategory($category);
        }

        return $dish;
    }

    /**
     * Create a dishvariation with random title
     * @param Dish $dish
     * @return DishVariation
     */
    protected function createDishVariation(Dish $dish = null)
    {
        if (!$dish instanceof \Mealz\MealBundle\Entity\Dish) {
            $dish = $this->createDish();
        }
        $dishVariation = new DishVariation();
        $dishVariation->setTitleEn('Test Variation EN ' . rand());
        $dishVariation->setTitleDe('Test Variation DE ' . rand());
        $dishVariation->setParent($dish);
        $dishVariation->setPrice(7.15);

        return $dishVariation;
    }

    /**
     * Create a new Meal object
     * @param \Mealz\MealBundle\Entity\Dish $dish
     * @return Meal
     */
    protected function createMeal(Dish $dish = NULL)
    {
        $meal = new Meal();
        $meal->setDish($dish ?: $this->createDish());
        $meal->setDateTime(new \DateTime());
        $meal->setPrice(1.23);

        return $meal;
    }

    /**
     * Create new a Profile object with random name
     * @return Profile
     */
    protected function createProfile()
    {
        $rand = rand();
        $profile = new Profile();
        $profile->setUsername('TestUsername' . $rand);
        $profile->setName('TestName' . $rand);
        $profile->setFirstName('TestFirstName' . $rand);

        return $profile;
    }

    /**
     * Create a new Login object
     * @param Profile|null $profile
     * @return Login
     */
    protected function createLogin(Profile $profile = null)
    {
        $name = $profile ? $profile->getName() : rand();
        $login = new Login();
        $login->setUsername($name);
        $login->setSalt(md5(uniqid(null, true)));

        /** @var PasswordEncoderInterface $encoder */
        $encoder = static::$kernel->getContainer()->get('security.encoder_factory')->getEncoder($login);
        $login->setPassword($encoder->encodePassword($name, $login->getSalt()));

        if ($profile) {
            $login->setProfile($profile);
        }

        return $login;
    }

    /**
     * Create a new Category object with random name
     * @return Category
     */
    protected function createCategory()
    {
        $category = new Category();
        $category->setTitleDe('Title DE ' . rand());
        $category->setTitleEn('Title EN ' . rand());

        return $category;
    }

    /**
     * Method to persist all given entities
     * @param array $entities
     */
    public function persistAndFlushAll($entities)
    {
        $em = $this->getDoctrine()->getManager();

        $em->transactional(function ($em) use ($entities) {
            /** @var EntityManager $em */
            // transaction is need for Participant entities
            foreach ($entities as $entity) {
                $em->persist($entity);
            }
            $em->flush();
        });
    }
}