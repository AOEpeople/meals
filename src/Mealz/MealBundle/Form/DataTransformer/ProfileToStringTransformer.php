<?php

namespace App\Mealz\MealBundle\Form\DataTransformer;

use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProfileToStringTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $objectManager;

    public function __construct(EntityManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function transform($value): string
    {
        if (null === $value || !$value instanceof Profile) {
            return '';
        }

        return $value->getUsername();
    }

    /**
     * @param string $value Username
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform($value): ?Profile
    {
        if (null === $value || !is_string($value)) {
            return null;
        }

        $profile = $this->objectManager->getRepository(Profile::class)->findOneBy(['username' => $value]);

        if (null === $profile) {
            throw new TransformationFailedException(sprintf('A %s with username "%s" does not exist!', 'profile', $value));
        }

        return $profile;
    }
}