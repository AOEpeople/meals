<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Controller\Payment;

use App\Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use App\Mealz\AccountingBundle\Repository\TransactionRepositoryInterface;
use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadCombinations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadParticipants;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadSlots;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Profile;
use Override;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class CashControllerTest extends AbstractControllerTestCase
{
    protected Wallet $wallet;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
            new LoadCategories(),
            new LoadWeeks(),
            new LoadDays(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(),
            new LoadSlots(),
            new LoadCombinations(self::getContainer()->get(EventDispatcherInterface::class)),
            new LoadParticipants(),
            new LoadTransactions(),
        ]);

        $participantRepo = self::getContainer()->get(ParticipantRepositoryInterface::class);
        $transactionRepo = self::getContainer()->get(TransactionRepositoryInterface::class);
        $this->wallet = new Wallet($participantRepo, $transactionRepo);
    }

    public function testPostPaymentCash(): void
    {
        $this->loginAs(self::USER_KITCHEN_STAFF);

        $profileRepo = $this->getDoctrine()->getRepository(Profile::class);
        $janeProfile = $profileRepo->findOneBy(['username' => self::USER_STANDARD]);

        $amount = 10;
        $balanceBefore = $this->wallet->getBalance($janeProfile);

        // Request
        $this->client->request('POST', '/api/payment/cash/' . (string) $janeProfile->getId() . '?amount=' . $amount);
        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($amount, $response);

        $newBalance = round($this->wallet->getBalance($janeProfile) - $balanceBefore);
        $this->assertEquals($amount, $newBalance);
    }
}
