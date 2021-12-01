<?php

namespace App\Mealz\MealBundle\Validator\Constraints;

use App\Mealz\MealBundle\Entity\Dish;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class DishConstraintValidator extends ConstraintValidator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($entity, Constraint $constraint): void
    {
        if ($entity->getDish() instanceof Dish) {
            return;
        }

        $unitOfWork = $this->entityManager->getUnitOfWork();
        $day = $entity->getDay();

        if (UnitOfWork::STATE_NEW !== $unitOfWork->getEntityState($entity) &&
            0 == $entity->getParticipants()->count()
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
