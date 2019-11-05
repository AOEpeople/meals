<?php

namespace Mealz\MealBundle\Form\DataTransformer;

use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class ProfileToStringTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param Profile $profile
     *
     * @return string
     */
    public function transform($profile)
    {
        if (null === $profile || !$profile instanceof Profile) {
            return '';
        }

        return $profile->getUsername();
    }

    /**
     * @param string $username
     *
     * @throws TransformationFailedException
     *
     * @return null | Profile
     */
    public function reverseTransform($username)
    {
        if ($username === null) {
            return null;
        }

        $profile = $this->objectManager->getRepository('MealzUserBundle:Profile')->findOneBy(array("username" => $username));

        if (null === $profile) {
            throw new TransformationFailedException(
                sprintf(
                    'A %s with username "%s" does not exist!',
                    'profile',
                    $username
                )
            );
        }

        return $profile;
    }
}
