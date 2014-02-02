<?php

namespace Xopn\DoctrineFileBundle\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * An annotation to mark an entity field that should be handled by
 * Xopn\DoctrineFileBundle\Service\FileListener
 *
 * @Annotation
 */
class File extends Annotation {
	public $path = '';
}