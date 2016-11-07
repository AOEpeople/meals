<?php

namespace Mealz\MealBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Day;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DayNameToIdTransformer implements DataTransformerInterface
{

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param mixed $value
     * @return int|string
     */
    public function transform($value)
    {
        if (null === $value) {
            return "";
        }

        return $value->getId();
    }


    /**
     * @param mixed $id
     * @return Day|null
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $day = $this->om->getRepository('MealzMealBundle:Day')->find($id);

        if (null === $day) {
            throw new TransformationFailedException(
                sprintf(
                    'A entity with id "%s" does not exist!',
                    $id
                )
            );
        }

        return $day;
    }
}