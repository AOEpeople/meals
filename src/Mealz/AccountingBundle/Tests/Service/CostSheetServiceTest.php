<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Service;

use App\Mealz\AccountingBundle\Service\CostSheetService;
use PHPUnit\Framework\TestCase;

class CostSheetServiceTest extends TestCase
{
    public function testMergeArrayByKey()
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

    public function testMergeDoubleUsers()
    {
        $costSheetService = new CostSheetService();

        $testArr = [
            'users' => [
                'admin.meals' => [
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
                'admin.meals@aoe.com' => [
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
                'test.meals' => [
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
                'test.meals' => [
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
                ],
                'admin.meals@aoe.com' => [
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
                ],
            ],
        ];

        $fncOutput['users'] = $costSheetService->mergeDoubleUserTransactions($testArr['users']);

        $this->assertEquals($expectedArr, $fncOutput);
    }
}
