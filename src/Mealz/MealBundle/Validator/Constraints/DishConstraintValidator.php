<?php

namespace Mealz\MealBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\Dish;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\UnitOfWork;

/**
 * @Annotation
 */
class DishConstraintValidator extends ConstraintValidator
{
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function validate($entity, Constraint $constraint)
    {
        if ($entity->getDish() instanceof Dish) {
            return;
        }

        $unitOfWork = $this->em->getUnitOfWork();
        $day = $entity->getDay();

        if ($unitOfWork->getEntityState($entity) !== UnitOfWork::STATE_NEW &&
            $entity->getParticipants()->count() == 0
        ) {
            $this->em->remove($entity);
            $this->em->flush($entity);
            $day->getMeals()->removeElement($entity);
        } else {
            $this->em->refresh($entity);
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%dish%', $entity->getDish())
                ->setParameter('%day%', $day)
                ->addViolation();
        }
    }
}