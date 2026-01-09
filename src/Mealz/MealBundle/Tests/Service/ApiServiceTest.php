<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\AccountingBundle\Entity\Price;
use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Repository\TransactionRepositoryInterface;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Service\ApiService;
use App\Mealz\MealBundle\Service\EventParticipationServiceInterface;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Mealz\MealBundle\Service\ApiService
 */
final class ApiServiceTest extends TestCase
{
    private MockObject $participantRepositoryMock;
    private MockObject $transactionRepositoryMock;
    private MockObject $mealRepositoryMock;
    private MockObject $dayRepositoryMock;
    private MockObject $eventParticipationServiceMock;
    private ApiService $apiService;

    #[Override]
    protected function setUp(): void
    {
        $this->participantRepositoryMock = $this->getMockBuilder(ParticipantRepositoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->transactionRepositoryMock = $this->getMockBuilder(TransactionRepositoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->mealRepositoryMock = $this->getMockBuilder(MealRepositoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->dayRepositoryMock = $this->getMockBuilder(DayRepositoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->eventParticipationServiceMock = $this->getMockBuilder(EventParticipationServiceInterface::class)->disableOriginalConstructor()->getMock();
        $this->apiService = new ApiService(
            $this->participantRepositoryMock,
            $this->transactionRepositoryMock,
            $this->mealRepositoryMock,
            $this->dayRepositoryMock,
            $this->eventParticipationServiceMock
        );
    }

    public function testGetFullTransactionHistoryIsValid(): void
    {
        $dateTimeBefore = new DateTime('02.12.2025');
        $dateTimeAfter = new DateTime('09.12.2025');
        $profile = new Profile();

        $dish = new Dish();
        $dish->setSlug(Dish::COMBINED_DISH_SLUG);
        $dish->setTitleDe('test de');
        $dish->setTitleEn('test en');
        $price = new Price();
        $price->setPriceValue(1);
        $price->setPriceCombinedValue(2);
        $day = new Day();
        $meal = new Meal($dish, $price, $day);
        $meal->setId(1);
        $meal->setDateTime($dateTimeBefore);
        $participant = new Participant($profile, $meal);
        $dish2 = new Dish();
        $dish2->setSlug('test');
        $dish2->setTitleDe('test 2 de');
        $dish2->setTitleEn('test 2 en');
        $price2 = new Price();
        $price2->setPriceValue(1);
        $price2->setPriceCombinedValue(2);
        $day2 = new Day();
        $meal2 = new Meal($dish2, $price2, $day2);
        $meal2->setId(1);
        $meal2->setDateTime($dateTimeBefore);
        $participant2 = new Participant($profile, $meal2);
        $participants = [$participant, $participant2];
        $this->participantRepositoryMock->expects(self::once())
            ->method('getParticipantsOnDays')
            ->with($dateTimeBefore, $dateTimeAfter, $profile)
            ->willReturn($participants);
        $transaction = new Transaction();
        $transaction->setAmount(1);
        $transaction->setDate($dateTimeBefore);
        $transaction->setPaymethod('test');
        $transactions = [$transaction];
        $this->transactionRepositoryMock->expects(self::once())
            ->method('getSuccessfulTransactionsOnDays')
            ->with($dateTimeBefore, $dateTimeAfter, $profile)
            ->willReturn($transactions);

        $fullTransactionHistory = $this->apiService->getFullTransactionHistory($dateTimeBefore, $dateTimeAfter, $profile);

        $this->assertEquals([
            -2.0,
            [
                [
                    'type' => 'credit',
                    'timestamp' => 1764630000,
                    'date' => $dateTimeBefore,
                    'description_en' => 'test',
                    'description_de' => 'test',
                    'amount' => 1.0
                ],
                [
                    'type' => 'debit',
                    'timestamp' => '1764630000-1',
                    'date' => $dateTimeBefore,
                    'description_en' => 'test en',
                    'description_de' => 'test de',
                    'amount' => 2.0
                ],
                [
                    'type' => 'debit',
                    'timestamp' => '1764630000-1',
                    'date' => $dateTimeBefore,
                    'description_en' => 'test 2 en',
                    'description_de' => 'test 2 de',
                    'amount' => 1.0
                ],
            ]
        ], $fullTransactionHistory);
    }
}
