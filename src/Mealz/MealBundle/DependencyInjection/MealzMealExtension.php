<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MealzMealExtension extends ConfigurableExtension
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter(
            'current_week',
            date('Y\\WW', strtotime('this sunday'))
        );

        $container->getDefinition('mealz_meal.notifier.mattermost')
            ->setArgument('$enabled', $mergedConfig['notifier']['mattermost']['enabled'])
            ->setArgument('$webhookURL', $mergedConfig['notifier']['mattermost']['webhook_url'])
            ->setArgument('$username', $mergedConfig['notifier']['mattermost']['username'])
            ->setArgument('$appName', $mergedConfig['notifier']['mattermost']['app_name'])
        ;
    }
}
