<?php

namespace App\Mealz\MealBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Mealz\MealBundle\Entity\LocalizedRepository;
use App\Mealz\MealBundle\EventListener\LocalisationListener;

class LocalizedRepositoryFactory
{
    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;

    /** @var LocalisationListener $localisationListener */
    protected $localisationListener;

    public function __construct(EntityManagerInterface $entityManager, LocalisationListener $localisationListener)
    {
        $this->entityManager = $entityManager;
        $this->localisationListener = $localisationListener;
    }

    public function createLocalizedRepository($repositoryName)
    {
        /** @var LocalizedRepository $repository */
        $repository = $this->entityManager->getRepository($repositoryName);
        $repository->setLocalizationListener($this->localisationListener);
        return $repository;
    }
}
