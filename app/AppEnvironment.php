<?php

/**
 * read the applications environment from environment variable or the DEFAULT_SYMFONY_ENV file
 */
class AppEnvironment {

	/**
	 * @var string
	 */
	protected $environment;

	/**
	 * @var string
	 */
	protected static $environmentFilePath = __DIR__;

	/**
	 * @param string $environmentFilePath path to the file where the environment is read from
	 * @param string $environment
	 */
	protected function __construct($environmentFilePath = NULL, $environment = NULL) {
		if (!$environment) {
			$environment = $this->read($environmentFilePath);
		}
		$this->init($environment);
	}

	/**
	 * @param string $filePath
	 */
	public static function setEnvironmentFilePath($filePath) {
		static::$environmentFilePath = rtrim($filePath, '/');
	}

	/**
	 * @param $environment
	 * @return AppEnvironment
	 */
	public static function fromString($environment) {
		return new static(NULL, $environment);
	}

	/**
	 * @param $environmentFilePath
	 * @return AppEnvironment
	 */
	public static function fromFile($environmentFilePath) {
		return new static($environmentFilePath);
	}

	/**
	 * @return AppEnvironment
	 */
	public static function fromDefault() {
		$SYMFONY_ENV = getenv('SYMFONY_ENV');
		if (is_string($SYMFONY_ENV) && $SYMFONY_ENV !== '') {
			return static::fromString($SYMFONY_ENV);
		} else {
			return static::fromFile(static::$environmentFilePath . DIRECTORY_SEPARATOR . 'DEFAULT_ENV');
		}
	}

	/**
	 * @param $environment
	 * @return bool
	 */
	public function isValidEnvironmentString($environment) {
		return preg_match('/^(?:[a-z]{3,16}_)?[a-z]{2,16}$/', $environment) === 1;
	}

	/**
	 * @param $filePath
	 * @return string
	 */
	protected function read($filePath) {
		if (!file_exists($filePath)) {
			throw new \RuntimeException(sprintf('File at location %s not found', $filePath));
		}
		if (!is_file($filePath)) {
			throw new \RuntimeException(sprintf('%s is not a file', $filePath));
		}
		if (!is_readable($filePath)) {
			throw new \RuntimeException(sprintf('File at location %s is not readable', $filePath));
		}

		$environment = file_get_contents($filePath);
		if (is_string($environment)) {
			$environment = trim($environment);
		}

		if (!$this->isValidEnvironmentString($environment)) {
			throw new \InvalidArgumentException(sprintf('Default environment read from %s seems formally invalid', $filePath));
		}

		return $environment;
	}

	/**
	 * @param string $environment
	 */
	protected function init($environment) {
		if (!$this->isValidEnvironmentString($environment)) {
			throw new \InvalidArgumentException(sprintf('The environment string is not formally valid'));
		}

		$this->environment = $environment;
	}

	/**
	 * @return string
	 */
	public function getEnvironment() {
		return $this->environment;
	}
}
