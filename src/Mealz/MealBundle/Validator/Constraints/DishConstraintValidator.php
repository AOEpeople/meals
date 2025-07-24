<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Validator\Constraints;

use App\Mealz\MealBundle\Entity\NullDish;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
final class DishConstraintValidator extends ConstraintValidator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Override]
    public function validate($value, Constraint $constraint): void
    {
        if (!($value->getDish() instanceof NullDish)) {
            return;
        }

        $unitOfWork = $this->entityManager->getUnitOfWork();
        $day = $value->getDay();

        if (UnitOfWork::STATE_NEW !== $unitOfWork->getEntityState($value)
            && 0 === $value->getParticipants()->count()
        ) {
            $meals = $day->getMeals();
            $meals->removeElement($value);
            $day->setMeals($meals);

            $this->entityManager->remove($value);
        } else {
            $this->entityManager->refresh($value);
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%dish%', $value->getDish()->getTitle())
                ->setParameter('%day%', $day->getDateTime()->format('l'))
                ->addViolation();
        }
    }
}
