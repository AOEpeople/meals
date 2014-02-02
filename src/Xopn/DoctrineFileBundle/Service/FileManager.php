<?php

namespace Xopn\DoctrineFileBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerAware;
use Xopn\DoctrineFileBundle\File\File;

/**
 * a Doctrine Listener that detects UploadedFile and persists them when the related
 * record is updated
 */
class FileManager extends ContainerAware {

	protected $symfonyRootDir;

	public function __construct($symfonyRootDir) {
		$this->symfonyRootDir = $symfonyRootDir;
	}

	/**
	 * @param $fileName
	 * @param $filePath
	 * @return File
	 */
	public function convertToObject($fileName, $filePath) {
		$filePath = $this->sanitizeDirectoryName($filePath);
		$file = new File($filePath . DIRECTORY_SEPARATOR . $fileName);
		$file->setSymfonyRootDir($this->symfonyRootDir);

		return $file;
	}

	/**
	 * delete a file
	 *
	 * @param \SplFileInfo|string $filePath
	 */
	public function delete($filePath) {
		if($filePath instanceof \SplFileInfo) {
			$filePath = $filePath->getPathname();
		}
		if(file_exists($filePath)) {
			unlink($filePath);
		}
	}

	/**
	 * move an UploadedFile and return the path it is stored in
	 *
	 * @param $file UploadedFile
	 * @param $path string
	 * @return string
	 */
	public function move(UploadedFile $file, $targetDirectory = 'uploads/') {
		// sanitize input data
		$originalName = $this->sanitizeOriginalName($file->getClientOriginalName());
		$targetDirectory = rtrim($targetDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$targetDirectory = $this->sanitizeDirectoryName($targetDirectory);
		$extension = $file->guessExtension();

		// create target directory
		if (!is_dir($targetDirectory)) {
            $ret = mkdir($targetDirectory, 0777, true);
            if (!$ret) {
                throw new \RuntimeException("Could not create target directory to move temporary file into.");
            }
        }

		// find a file name
		do {
			$fileName = $this->suggestFileName($originalName, $extension);

		} while(file_exists($targetDirectory . $fileName));

		//
		$file->move($targetDirectory, $fileName);
		return $fileName;
	}

	/**
	 * suggests a filename to be used by the uploaded file
	 * it has to be checked if such a file exists though
	 *
	 * @param $baseName string
	 * @param $extension string
	 * @return string
	 */
	protected function suggestFileName($baseName, $extension) {
		return sprintf('%s_%s.%s',$baseName, dechex(rand()), $extension);
	}

	/**
	 * @param $originalName
	 * @return string
	 */
	protected function sanitizeOriginalName($originalName) {
		$rpos = strrpos($originalName, '.');
		if($rpos !== NULL) {
			$originalName = substr($originalName, 0, $rpos);
		}
		return str_replace('.', '_', $originalName);
	}

	/**
	 * @param $directoryName string
	 * @return string
	 */
	protected function sanitizeDirectoryName($directoryName) {
		$container = $this->container;
		return preg_replace_callback('/%[\w\p{P}]+%/', function ($match) use($container) {
			return $container->getParameter(trim($match[0], '%'));
		}, $directoryName);
	}
}