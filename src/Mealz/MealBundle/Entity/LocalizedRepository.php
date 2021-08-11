<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use App\Mealz\MealBundle\EventListener\LocalisationListener;

abstract class LocalizedRepository extends EntityRepository
{
    protected LocalisationListener $localizationListener;

    public function setLocalizationListener(LocalisationListener $localisationListener): void
    {
        $this->localizationListener = $localisationListener;
    }
}
