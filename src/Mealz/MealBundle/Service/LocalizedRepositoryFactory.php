<?php

namespace Mealz\MealBundle\Service;

use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\LocalizedRepository;
use Mealz\MealBundle\EventListener\LocalisationListener;

class LocalizedRepositoryFactory
{
    /** @var EntityManager $entityManager */
    protected $entityManager;

    /** @var LocalisationListener $localisationListener */
    protected $localisationListener;

    public function __construct(EntityManager $entityManager, LocalisationListener $localisationListener)
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
