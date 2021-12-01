<?php

namespace App\Mealz\MealBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class EntityToIdTransformer.
 */
class EntityToIdTransformer implements DataTransformerInterface
{
    protected EntityManagerInterface $objectManager;

    protected string $class;

    public function __construct(EntityManagerInterface $objectManager, string $class)
    {
        $this->objectManager = $objectManager;
        $this->class = $class;
    }

    /**
     * transform Entity to Id.
     *
     * @param mixed $entity
     *
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
     * transform Id to Entity.
     *
     * @param mixed $identifier
     *
     * @return object|null
     */
    public function reverseTransform($identifier)
    {
        if (false === isset($identifier)) {
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
