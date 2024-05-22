<?php

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Service\Workday;
use PHPUnit\Framework\TestCase;

class WorkdayTest extends TestCase
{
    /**
     * @dataProvider getDataForTestBasic
     */
    public function testBasic($givenDate, $expectedDate): void
    {
        $workdayService = new Workday();

        $response = $workdayService->getNextWorkday($givenDate);

        $this->assertSame(
            $expectedDate,
            $response->format('Y-m-d')
        );
    }

    /**
     * @return string[][]
     *
     * @psalm-return array<string, array{0: string, 1: string, 2: string}>
     */
    public function getDataForTestBasic(): array
    {
        $data = [
            // thursday
            ['2014-01-02', '2014-01-03', 'basic test'],
            ['2014-01-03', '2014-01-06', 'weekends will be skipped'],
            ['2013-12-23', '2013-12-27', 'blacklisted days will be skipped'],
            ['2013-12-31', '2014-01-02', 'switching years works'],
            ['2014-04-17', '2014-04-22', 'easter will be skipped'],
            ['2014-05-28', '2014-05-30', 'feast of ascension will be skipped'],
            ['2014-06-06', '2014-06-10', 'pentecost will be skipped'],
        ];

        return array_combine(
            array_map(function ($row) {
                return $row[2];
            }, $data),
            $data
        );
    }
}
