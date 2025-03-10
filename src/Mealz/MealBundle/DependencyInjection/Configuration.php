<?php

namespace App\Mealz\MealBundle\DependencyInjection;

use Override;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-return TreeBuilder<'array'>
     */
    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        return new TreeBuilder('mealz_meal');
    }
}
