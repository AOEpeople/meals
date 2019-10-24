<?php

namespace Mealz\MealBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class EntityToIdTransformer
 * @package Mealz\MealBundle\Form\DataTransformer
 */
class EntityToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var string
     */
    protected $class;

    /**
     * EntityToIdTransformer constructor.
     * @param ObjectManager $objectManager
     * @param $class
     */
    public function __construct(ObjectManager $objectManager, $class)
    {
        $this->objectManager = $objectManager;
        $this->class = $class;
    }

    /**
     * transform Entity to Id
     * @param mixed $entity
     * @return string
     */
    public function transform($entity)
    {
        if (null === $entity) {
            return '';
        }

        return $entity->getId();
    }

    /**
     * transform Id to Entity
     * @param mixed $identifier
     * @return null|object
     */
    public function reverseTransform($identifier)
    {
        if (isset($identifier) === false) {
            return null;
        }
        $entity = $this->objectManager
            ->getRepository($this->class)
            ->find($identifier);
        if (null === $entity) {
            throw new TransformationFailedException();
        }

        return $entity;
    }
}
