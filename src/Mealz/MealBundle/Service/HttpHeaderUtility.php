<?php

namespace Mealz\MealBundle\Service;

class HttpHeaderUtility {

	protected $locales = array();

	public function setLocales(array $locales) {
		$this->locales = $locales;
	}

	/**
	 * parse an Accept-Language header and returns the best matching locale from $this->locales
	 *
	 * @param $headerString
	 * @throws \InvalidArgumentException
	 * @return mixed
	 */
	public function getLocaleFromAcceptLanguageHeader($headerString) {
		if(empty($this->locales)) {
			throw new \InvalidArgumentException(sprintf(
				'%s::locales is empty. Set some using the setLocales method.', get_class($this)
			));
		}
		$headerString = trim($headerString);

		if(empty($headerString)) {
			return reset($this->locales);
		}
		$acceptLanguages = explode(',', $headerString);

		$orderedAcceptLanguages = $this->filterAndOrderAcceptLanguages($acceptLanguages);

		return empty($orderedAcceptLanguages) ? reset($this->locales) : reset($orderedAcceptLanguages);
	}

	/**
	 * filter an Accept-Language header string according to the given quality and drop
	 * languages that are not supported in $this->locales
	 *
	 * @param $acceptLanguages
	 * @return array
	 */
	protected function filterAndOrderAcceptLanguages($acceptLanguages) {
		$orderedAcceptLanguages = array();
		foreach($acceptLanguages as $acceptLanguage) {
			$parts = explode(';', $acceptLanguage);
			$acceptLanguage = strtolower(trim($parts[0]));
			if($acceptLanguage == '*') {
				$acceptLanguage = reset($this->locales);
			}
			if(!in_array($acceptLanguage, $this->locales)) {
				$acceptLanguage = $this->parseShortAcceptLanguage($acceptLanguage);
				if(!in_array($acceptLanguage, $this->locales)) {
					continue;
				}
			}
			$quality = isset($parts[1]) ? $this->parseQuality(trim($parts[1])) : 1;
			if($quality == 0) {
				continue;
			}
			$quality = intval($quality * 1000);
			while(array_key_exists($quality, $orderedAcceptLanguages)) {
				$quality--;
			}
			$orderedAcceptLanguages[$quality] = $acceptLanguage;
		}
		krsort($orderedAcceptLanguages);
		return $orderedAcceptLanguages;
	}

	/**
	 * get the part before the first hyphen ("-") in a language string
	 *
	 * @param $acceptLanguage
	 * @return string
	 */
	protected function parseShortAcceptLanguage($acceptLanguage) {
		$rpos = strrpos($acceptLanguage, '-');
		if($rpos !== FALSE) {
			return substr($acceptLanguage, 0, $rpos);
		} else {
			return $acceptLanguage;
		}
	}

	/**
	 * parse the quality string after a language string
	 *
	 * @param $qualityString
	 * @return float|int
	 */
	protected function parseQuality($qualityString) {
		$rpos = strrpos($qualityString, '=');
		if($rpos !== FALSE) {
			return (float)substr($qualityString, $rpos+1);
		}
		return 1;
	}




}