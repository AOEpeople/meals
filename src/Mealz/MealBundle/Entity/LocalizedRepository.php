<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use App\Mealz\MealBundle\EventListener\LocalisationListener;
use Doctrine\ORM\EntityRepository;

abstract class LocalizedRepository extends EntityRepository
{
    protected ?LocalisationListener $localizationListener = null;

    public function setLocalizationListener(LocalisationListener $localisationListener): void
    {
        $this->localizationListener = $localisationListener;
    }
}
