<?php

namespace App\Mealz\MealBundle\Asset\VersionStrategy;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class HashVersionStrategy implements VersionStrategyInterface
{
    public function getVersion($path)
    {
        return substr(md5_file($path), 0, 7);
    }

    public function applyVersion($path)
    {
        return true === file_exists($path) ? sprintf('%s?v=%s', $path, $this->getVersion($path)) : '';
    }
}
