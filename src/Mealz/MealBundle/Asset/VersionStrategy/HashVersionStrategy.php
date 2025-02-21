<?php

namespace App\Mealz\MealBundle\Asset\VersionStrategy;

use Override;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final class HashVersionStrategy implements VersionStrategyInterface
{
    #[Override]
    public function getVersion($path): string
    {
        return substr(md5_file($path), 0, 7);
    }

    #[Override]
    public function applyVersion($path): string
    {
        return true === file_exists($path) ? sprintf('%s?v=%s', $path, $this->getVersion($path)) : '';
    }
}
