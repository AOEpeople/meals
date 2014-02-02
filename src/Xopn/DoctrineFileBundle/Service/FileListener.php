<?php

namespace Xopn\DoctrineFileBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Annotations\AnnotationReader;
use Xopn\DoctrineFileBundle\Service\FileManager;

/**
 * a Doctrine Listener that detects UploadedFile and persists them when the related
 * record is updated
 */
class FileListener {

	/**
	 * @var AnnotationReader
	 */
	protected $annotationReader;

	/**
	 * @var FileManager
	 */
	protected $fileManager;

	/**
	 * @param $annotationReader AnnotationReader
	 * @param $fileManager      FileManager
	 */
	public function __construct($annotationReader, $fileManager) {
		$this->annotationReader = $annotationReader;
		$this->fileManager = $fileManager;
	}


	public function postLoad(LifecycleEventArgs $eventArgs) {
		$entity = $eventArgs->getEntity();
		$reflectionClass = new \ReflectionClass(get_class($entity));

		// check each changed field if it has the File annotation
		foreach($reflectionClass->getProperties() as $reflectionProperty) {
			$annotation = $this->annotationReader->getPropertyAnnotation(
				$reflectionProperty,
				'Xopn\\DoctrineFileBundle\\Annotations\\File'
			);
			if($annotation === NULL) {
				continue;
			}

			$getter = 'get' . ucfirst($reflectionProperty->getName());

			$value = call_user_func(array($entity, $getter));

			if(!empty($value)) {
				$setter = 'set' . ucfirst($reflectionProperty->getName());
				call_user_func(array($entity, $setter), $this->fileManager->convertToObject($value, $annotation->path));
			}
		}
	}

	/**
	 * called before an object is INSERTed
	 *
	 * @param \Doctrine\ORM\Event\LifecycleEventArgs $eventArgs
	 */
	public function prePersist(LifecycleEventArgs $eventArgs) {
		$entity = $eventArgs->getEntity();
		$reflectionClass = new \ReflectionClass(get_class($entity));

		// check each changed field if it has the File annotation
		foreach($reflectionClass->getProperties() as $reflectionProperty) {
			$annotation = $this->annotationReader->getPropertyAnnotation(
				$reflectionProperty,
				'Xopn\\DoctrineFileBundle\\Annotations\\File'
			);
			if($annotation === NULL) {
				continue;
			}

			$getter = 'get' . ucfirst($reflectionProperty->getName());

			$value = call_user_func(array($entity, $getter));

			// move a new file
			if($value instanceof UploadedFile) {
				$setter = 'set' . ucfirst($reflectionProperty->getName());
				call_user_func(array($entity, $setter), $this->fileManager->move($value, $annotation->path));
			}
		}
	}

	/**
	 * called before an object id DELETEd
	 *
	 * @param \Doctrine\ORM\Event\PreUpdateEventArgs $eventArgs
	 */
	public function preRemove(PreUpdateEventArgs $eventArgs) {
		$entity = $eventArgs->getEntity();
		$reflectionClass = new \ReflectionClass(get_class($entity));

		// check each changed field if it has the File annotation
		foreach($reflectionClass->getProperties() as $reflectionProperty) {
			$annotation = $this->annotationReader->getPropertyAnnotation(
				$reflectionProperty,
				'Xopn\\DoctrineFileBundle\\Annotations\\File'
			);
			if($annotation === NULL) {
				continue;
			}
			$getter = 'get' . ucfirst($reflectionProperty->getName());

			$value = call_user_func(array($entity, $getter));

			// move a new file
			if(!empty($value)) {
				$this->fileManager->delete($value);
			}
		}
	}

	/**
	 * called before an object is UPDATEd
	 *
	 * @param \Doctrine\ORM\Event\PreUpdateEventArgs $eventArgs
	 */
	public function preUpdate(PreUpdateEventArgs $eventArgs) {
		$entity = $eventArgs->getEntity();
		$reflectionClass = new \ReflectionClass(get_class($entity));

		// check each changed field if it has the File annotation
		foreach(array_keys($eventArgs->getEntityChangeSet()) as $fieldName) {
			$reflectionProperty = $reflectionClass->getProperty($fieldName);
			$annotation = $this->annotationReader->getPropertyAnnotation(
				$reflectionProperty,
				'Xopn\\DoctrineFileBundle\\Annotations\\File'
			);
			if($annotation === NULL) {
				continue;
			}
			$oldValue = $eventArgs->getOldValue($fieldName);
			$newValue = $eventArgs->getNewValue($fieldName);

			if($newValue instanceof \SplFileInfo && !($newValue instanceof UploadedFile)) {
				$newValue = $newValue->getBasename();
				$eventArgs->setNewValue($fieldName, $newValue);
			}

			if($oldValue == $newValue) {
				continue;
			}

			// remove an old file
			if($oldValue) {
				$this->fileManager->delete(
					$oldValue instanceof \SplFileInfo ? $oldValue : $this->fileManager->convertToObject($oldValue, $annotation->path)
				);
			}
			// move a new file
			if($newValue instanceof UploadedFile) {
				$eventArgs->setNewValue($fieldName, $this->fileManager->move($newValue, $annotation->path));
			}
		}
	}
}