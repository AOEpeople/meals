<?php

namespace Mealz\MealBundle\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Mealz\MealBundle\Entity\Dish;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\UnitOfWork;

/**
 * @Annotation
 */
class DishConstraintValidator extends ConstraintValidator
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($entity, Constraint $constraint)
    {
        if ($entity->getDish() instanceof Dish) {
            return;
        }

        $unitOfWork = $this->entityManager->getUnitOfWork();
        $day = $entity->getDay();

        if ($unitOfWork->getEntityState($entity) !== UnitOfWork::STATE_NEW &&
            $entity->getParticipants()->count() == 0
        ) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush($entity);
            $day->getMeals()->removeElement($entity);
        } else {
            $this->entityManager->refresh($entity);
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%dish%', $entity->getDish())
                ->setParameter('%day%', $day)
                ->addViolation();
        }
    }
}
