<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Service;

use App\Mealz\AccountingBundle\Service\CostSheetService;
use PHPUnit\Framework\TestCase;

final class CostSheetServiceTest extends TestCase
{
    /**
     * @dataProvider sortingLocales
     */
    public function testSortUsersByName(string $locale): void
    {
        $users = [
            1 => ['id' => 1, 'name' => 'Zeller', 'firstName' => 'Anna'],
            2 => ['id' => 2, 'name' => 'Müller', 'firstName' => 'Zoe'],
            3 => ['id' => 3, 'name' => ' meier ', 'firstName' => 'Clara'],
            10 => ['id' => 10, 'name' => 'Äbel', 'firstName' => 'Berta'],
            20 => ['id' => 20, 'name' => 'abel', 'firstName' => 'Anton'],
            40 => ['id' => 40, 'name' => 'Müller', 'firstName' => 'Adam'],
            30 => ['id' => 30, 'name' => 'Müller', 'firstName' => ' adam '],
        ];

        $sortedUsers = (new CostSheetService())->sortUsersByName($users, $locale);

        $this->assertSame([20, 10, 3, 30, 40, 2, 1], array_keys($sortedUsers));
        $this->assertSame($users[20], $sortedUsers[20]);
        $this->assertSame($users[30], $sortedUsers[30]);
    }

    public function sortingLocales(): array
    {
        return [['de'], ['en']];
    }

    public function testSortUsersByNameReturnsEmptyList(): void
    {
        $this->assertSame([], (new CostSheetService())->sortUsersByName([], 'de'));
    }

    public function testMergeArrayByKey(): void
    {
        $costSheetService = new CostSheetService();

        $testArrOne = [
            'earlier' => 1,
            '1234567890' => 2.03,
            '1234567891' => 3,
            '1234567892' => 0,
            '1234567894' => -1,
            '1234567895' => -2.17,
            'total' => 17.05,
        ];

        $testArrTwo = [
            'earlier' => 2,
            '1234567890' => 3.75,
            '1234567891' => -7,
            '1234567892' => 3.37,
            '1234567894' => -5,
            '1234567895' => -3.72,
            'total' => 9.8,
        ];

        $expectedArr = [
            'earlier' => 3.00,
            '1234567890' => 5.78,
            '1234567891' => -4.00,
            '1234567892' => 3.37,
            '1234567894' => -6.00,
            '1234567895' => -5.89,
            'total' => 26.85,
        ];

        $fncOutput = $costSheetService->mergeArrayByKey($testArrOne, $testArrTwo);

        $this->assertEquals($expectedArr, $fncOutput, 'Arrays are not equal');
    }

    public function testMergeDoubleUsers(): void
    {
        $costSheetService = new CostSheetService();

        $testArr = [
            'users' => [
                1 => [
                    'id' => 1,
                    'username' => 'admin.meals',
                    'name' => 'Meals',
                    'firstName' => 'Admin',
                    'hidden' => false,
                    'costs' => [
                        'earlier' => 1,
                        '1234567890' => 2.03,
                        '1234567891' => 3,
                        '1234567892' => 0,
                        '1234567894' => -1,
                        '1234567895' => -2.17,
                        'total' => 17.05,
                    ],
                ],
                2 => [
                    'id' => 2,
                    'username' => 'admin.meals@aoe.com',
                    'name' => 'Meals',
                    'firstName' => 'Admin',
                    'hidden' => false,
                    'costs' => [
                        'earlier' => 2,
                        '1234567890' => 3.75,
                        '1234567891' => -7,
                        '1234567892' => 3.37,
                        '1234567894' => -5,
                        '1234567895' => -3.72,
                        'total' => 9.8,
                    ],
                ],
                3 => [
                    'id' => 3,
                    'username' => 'test.meals',
                    'name' => 'Meals',
                    'firstName' => 'Test',
                    'hidden' => false,
                    'costs' => [
                        'earlier' => 1,
                        '1234567890' => 2.51,
                        '1234567891' => -3,
                        '1234567892' => 0.37,
                        '1234567894' => -3.29,
                        '1234567895' => 6.72,
                        'total' => 28.43,
                    ],
                ],
            ],
        ];

        $expectedArr = [
            'users' => [
                2 => [
                    'name' => 'Meals',
                    'firstName' => 'Admin',
                    'hidden' => false,
                    'costs' => [
                        'earlier' => 3.00,
                        '1234567890' => 5.78,
                        '1234567891' => -4.00,
                        '1234567892' => 3.37,
                        '1234567894' => -6.00,
                        '1234567895' => -5.89,
                        'total' => 26.85,
                    ],
                    'id' => 2,
                    'username' => 'admin.meals@aoe.com',
                ],
                3 => [
                    'name' => 'Meals',
                    'firstName' => 'Test',
                    'hidden' => false,
                    'costs' => [
                        'earlier' => 1.00,
                        '1234567890' => 2.51,
                        '1234567891' => -3.00,
                        '1234567892' => 0.37,
                        '1234567894' => -3.29,
                        '1234567895' => 6.72,
                        'total' => 28.43,
                    ],
                    'id' => 3,
                    'username' => 'test.meals',
                ],
            ],
        ];

        $fncOutput['users'] = $costSheetService->mergeDoubleUserTransactions($testArr['users']);

        $this->assertEquals($expectedArr, $fncOutput);
    }
}
