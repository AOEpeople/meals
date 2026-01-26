<?php

namespace App\Mealz\MealBundle\Form\DataTransformer;

use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<Profile, string>
 */
final class ProfileToStringTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $objectManager;

    public function __construct(EntityManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    #[Override]
    public function transform($value): string
    {
        if (!$value instanceof Profile) {
            return '';
        }

        return $value->getUsername();
    }

    /**
     * @param string $value Username
     *
     * @throws TransformationFailedException
     */
    #[Override]
    public function reverseTransform($value): ?Profile
    {
        if (!is_string($value)) {
            return null;
        }

        $profile = $this->objectManager->getRepository(Profile::class)->findOneBy(['username' => $value]);

        if (null === $profile) {
            throw new TransformationFailedException(sprintf('A %s with username "%s" does not exist!', 'profile', $value));
        }

        return $profile;
    }
}
