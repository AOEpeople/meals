<?php

namespace App\Mealz\MealBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('mealz_meal');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('notifier')
                    ->children()
                        ->arrayNode('mattermost')
                            ->children()
                                ->booleanNode('enabled')
                                    ->info('Enable/disable sending of meal notifications to mattermost.')
                                    ->defaultFalse()
                                ->end()
                                ->scalarNode('webhook_url')
                                    ->info('Mattermost webhook URL')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('username')
                                    ->info('Friendly username displayed in mattermost notifications.')
                                    ->defaultValue('Chef')
                                ->end()
                                ->scalarNode('app_name')
                                    ->info('Application name displayed in mattermost notifications.')
                                    ->defaultValue('Meals')
                                ->end()
                            ->end()
                        ->end() // mattermost
                    ->end()
                ->end() // notification
            ->end()
        ;

        return $treeBuilder;
    }
}
