<?php


namespace Mealz\MealBundle\Service;

/**
 * logic to determine the next workday
 */
class Workday {

	/**
	 * array of weekdays that are considered workdays
	 *
	 * monday(1) to sunday(7)
	 *
	 * @var array
	 */
	protected $weekdayWhitelist = array('1', '2', '3', '4', '5');

	/**
	 * array of dates that are not considered workdays
	 *
	 * format "mm-dd"
	 *
	 * @var array
	 */
	protected $dayBlacklist = array(
		// new year
		'01-01',
		// christmas
		'12-24',
		'12-25',
		'12-26',
	);

	protected $dayBlacklistRelativeToEaster = array(
		'-2 days',
		'+ 0 days',
		'+1 day',
		'+39 days',
		'+50 days'
	);

	public function setWeekdayWhitelist($whitelist) {
		$this->weekdayWhitelist = $whitelist;
	}

	/**
	 * @param array $dayBlacklist
	 */
	public function setDayBlacklist($dayBlacklist) {
		$this->dayBlacklist = $dayBlacklist;
	}

	/**
	 * @param array $dayBlacklistRelativeToEaster
	 */
	public function setDayBlacklistRelativeToEaster($dayBlacklistRelativeToEaster) {
		$this->dayBlacklistRelativeToEaster = $dayBlacklistRelativeToEaster;
	}

	/**
	 * gives the next workday after the given date
	 *
	 * @param null|string|\DateTime $date
	 * @return \DateTime
	 */
	public function getNextWorkday($date = NULL) {
		if($date === NULL) {
			$date = new \DateTime();
		} elseif($date instanceof \DateTime) {
			$date = clone $date;
		} else {
			$date = new \DateTime($date);
		}

		while(true) {
			$date->modify('+1 day');
			if($this->dateIsValid($date)) {
				return $date;
			}
		}
	}

	protected function dateIsValid(\DateTime $date) {
		if(!in_array($date->format('N'), $this->weekdayWhitelist)) {
			return FALSE;
		}

		if(in_array($date->format('m-d'), $this->dayBlacklist)) {
			return FALSE;
		}

		$easter = new \DateTime(date('Y-m-d', easter_date($date->format('Y'))));
		foreach($this->dayBlacklistRelativeToEaster as $easterRelative) {
			$blacklistDay = clone $easter;
			$blacklistDay->modify($easterRelative);

			if($blacklistDay->format('m-d') == $date->format('m-d')) {
				return FALSE;
			}
		}

		return TRUE;

	}


}