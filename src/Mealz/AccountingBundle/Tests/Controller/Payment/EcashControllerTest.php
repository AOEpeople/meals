<?php

namespace Mealz\AccountingBundle\Tests\Controller\Payment;

use Mealz\AccountingBundle\Controller\Payment\EcashController;
use Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadParticipants;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;

class EcashControllerTest extends AbstractControllerTestCase
{

    /**
     * Set up the testing environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->createDefaultClient();
        $this->clearAllTables();
        $this->loadFixtures(
            array(
                new LoadCategories(),
                new LoadWeeks(),
                new LoadDays(),
                new LoadDishes(),
                new LoadDishVariations(),
                new LoadMeals(),
                new LoadParticipants(),
                new LoadRoles(),
                new LoadUsers($this->client->getContainer()),
                new LoadTransactions()
            )
        );
    }

    public function testGetPaymentFormForProfileAction()
    {

    }

    public function testPaymentFormHandlingAction()
    {

    }
}
