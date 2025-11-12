<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Account;

use App\Mealz\MealBundle\Account\DefaultAccountOrderLockedBalanceChecker;
use App\Mealz\MealBundle\Account\Model\Clock;
use App\Mealz\MealBundle\Service\ApiService;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @CoversClass DefaultAccountOrderLockedBalanceChecker
 */
final class DefaultAccountOrderLockedBalanceCheckerTest extends TestCase
{
    private MockObject $apiServiceMock;
    private Clock $clock;
    private DefaultAccountOrderLockedBalanceChecker $defaultAccountOrderLockedBalanceChecker;

    #[Override]
    protected function setUp(): void
    {
        $this->apiServiceMock = $this->getMockBuilder(ApiService::class)->disableOriginalConstructor()->getMock();
        $this->clock = new Clock();
        $this->defaultAccountOrderLockedBalanceChecker = new DefaultAccountOrderLockedBalanceChecker(
            $this->apiServiceMock,
            -50,
            $this->clock
        );
    }

    public function testCheckWithAccountOrderNotLocked(): void
    {
        $profile = new Profile();
        $dateFrom = new DateTime()->setTimestamp(0);
        $dateTo = $this->clock->now();
        $fullTransactionHistory = [
            2.54
        ];
        $this->apiServiceMock->expects(self::once())
            ->method('getFullTransactionHistory')
            ->with($dateFrom, $dateTo, $profile)
            ->willReturn($fullTransactionHistory);
        $isAccountOrderBlocked = $this->defaultAccountOrderLockedBalanceChecker->check($profile);

        self::assertFalse($isAccountOrderBlocked);
    }

    public function testCheckWithAccountOrderLocked(): void
    {
        $profile = new Profile();
        $dateFrom = new DateTime()->setTimestamp(0);
        $dateTo = $this->clock->now();
        $fullTransactionHistory = [
            -60
        ];
        $this->apiServiceMock->expects(self::once())
            ->method('getFullTransactionHistory')
            ->with($dateFrom, $dateTo, $profile)
            ->willReturn($fullTransactionHistory);
        $isAccountOrderBlocked = $this->defaultAccountOrderLockedBalanceChecker->check($profile);

        self::assertTrue($isAccountOrderBlocked);
    }

    public function testCheckWithInvalidFullTransactionHistory(): void
    {
        $profile = new Profile();
        $dateFrom = new DateTime()->setTimestamp(0);
        $dateTo = $this->clock->now();
        $fullTransactionHistory = [];
        $this->apiServiceMock->expects(self::once())
            ->method('getFullTransactionHistory')
            ->with($dateFrom, $dateTo, $profile)
            ->willReturn($fullTransactionHistory);
        $isAccountOrderBlocked = $this->defaultAccountOrderLockedBalanceChecker->check($profile);

        self::assertFalse($isAccountOrderBlocked);
    }
}
