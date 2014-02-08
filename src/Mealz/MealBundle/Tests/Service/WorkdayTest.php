<?php


namespace Mealz\MealBundle\Tests\Service;


use Mealz\MealBundle\Service\Workday;

class WorkdayTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @param $givenDate
	 * @param $expectedDate
	 * @dataProvider getDataForTestBasic
	 */
	public function testBasic($givenDate, $expectedDate) {
		$workdayService = new Workday();

		$response = $workdayService->getNextWorkday($givenDate);

		$this->assertSame(
			$expectedDate,
			$response->format('Y-m-d')
		);
	}

	public function getDataForTestBasic() {
		$data = array(
			// thursday
			array('2014-01-02', '2014-01-03', 'basic test'),
			array('2014-01-03', '2014-01-06', 'weekends will be skipped'),
			array('2013-12-23', '2013-12-27', 'blacklisted days will be skipped'),
			array('2013-12-31', '2014-01-02', 'switching years works'),
			array('2014-04-17', '2014-04-22', 'easter will be skipped'),
			array('2014-05-28', '2014-05-30', 'feast of ascension will be skipped'),
			array('2014-06-06', '2014-06-10', 'pentecost will be skipped'),
		);

		return array_combine(
			array_map(function($row) { return $row[2]; }, $data),
			$data
		);
	}

}
