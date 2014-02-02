<?php

namespace Xopn\DoctrineFileBundle\File;

use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class File extends SymfonyFile {

	/**
	 * @var string
	 */
	protected $symfonyRootDir;

	/**
	 * set the symfony root dir
	 *
	 * this is used to determine web paths etc
	 *
	 * @param $rootDir string
	 */
	public function setSymfonyRootDir($rootDir) {
		$this->symfonyRootDir = realpath($rootDir);
	}

	public function getAbsolutePath() {
		return $this->getRealPath();
	}

	/**
	 * get the path relative to the symfony root dir
	 *
	 * returns null if file is not below the symfony root dir
	 *
	 * @return null|string
	 */
	public function getRootPath() {
		return $this->stripPath($this->symfonyRootDir);
	}

	/**
	 * get the path relative to the symfony root dir
	 *
	 * returns null if file is not below the symfony root dir
	 *
	 * @return null|string
	 */
	public function getWebPath() {
		$path = $this->stripPath($this->symfonyRootDir.'/web');
		return $path ? '/' . $path : null;
	}

	protected function stripPath($basePath) {
		if(strncmp($this->getRealPath(), $basePath, strlen($basePath)) == 0) {
			// if: realPath starts with $basePath
			return ltrim(substr($this->getRealPath(), strlen($basePath)), '/');
		} else {
			return null;
		}
	}


}