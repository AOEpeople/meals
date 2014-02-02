<?php

namespace Xopn\DoctrineFileBundle\Tests\File;

use Xopn\DoctrineFileBundle\File\File;

class FileTest extends \PHPUnit_Framework_TestCase {

	protected $rootPath = '/tmp';

	public function setUp() {
		$this->rootPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'FileTest' . md5(__FILE__);
		@mkdir($this->rootPath, 0777, true);
	}

	public function tearDown() {
		$this->rrmdir($this->rootPath);
	}

	protected function createFile($path) {
		@mkdir(dirname($path), 0777, true);
		file_put_contents($path, __FILE__);
		$file = new File($path);
		$file->setSymfonyRootDir($this->rootPath);
		return $file;
	}

	protected function removeFile($file) {
		unlink($file instanceof File ? $file->getRealPath() : $file);
	}

	/**
	 * removes a directory recursively
	 *
	 * @param $dir
	 * @see http://www.php.net/manual/de/function.rmdir.php#98622
	 */
	protected function rrmdir($dir) {
	   if (is_dir($dir)) {
	     $objects = scandir($dir);
	     foreach ($objects as $object) {
	       if ($object != "." && $object != "..") {
	         if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
	       }
	     }
	     reset($objects);
	     rmdir($dir);
	   }
	 }

	public function testAbsolutePath() {
		$realPath = $this->rootPath . DIRECTORY_SEPARATOR . 'foo.txt';
		$file = $this->createFile($realPath);

		$this->assertEquals(
			$realPath,
			$file->getAbsolutePath(),
			'basic test'
		);

		$uglyPath = $this->rootPath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . basename($this->rootPath) . DIRECTORY_SEPARATOR . 'foo.txt';
		$file = $this->createFile($uglyPath);

		$this->assertEquals(
			$realPath,
			$file->getAbsolutePath(),
			'realpath test'
		);
	}

	public function testRootPath() {
		$realPath = $this->rootPath . DIRECTORY_SEPARATOR . 'foo.txt';
		$file = $this->createFile($realPath);

		$this->assertEquals(
			'foo.txt',
			$file->getRootPath(),
			'basic test'
		);

		$uglyPath = $this->rootPath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . basename($this->rootPath) . DIRECTORY_SEPARATOR . 'foo.txt';
		$file = $this->createFile($uglyPath);

		$this->assertEquals(
			'foo.txt',
			$file->getRootPath(),
			'realpath test'
		);
	}

	public function testFileOutsideRootPath() {
		$path = tempnam(sys_get_temp_dir(), 'FileTest');
		$file = $this->createFile($path);

		$this->assertSame(
			null,
			$file->getRootPath()
		);

		$this->removeFile($file);
	}

	public function testWebPath() {
		$realPath = $this->rootPath . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'foo.txt';
		$file = $this->createFile($realPath);

		$this->assertEquals(
			'/foo.txt',
			$file->getWebPath(),
			'basic test'
		);

		$uglyPath = $this->rootPath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . basename($this->rootPath) . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'foo.txt';
		$file = $this->createFile($uglyPath);

		$this->assertEquals(
			'/foo.txt',
			$file->getWebPath(),
			'realpath test'
		);
	}

	public function testFileOutsideWebPath() {
		$path = $this->rootPath . DIRECTORY_SEPARATOR . 'foo.txt';
		$file = $this->createFile($path);

		$this->assertSame(
			null,
			$file->getWebPath()
		);

		$this->removeFile($file);
	}


}