<?php

require_once __DIR__ . '/AppEnvironment.php';

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    /**
     * @var AppEnvironment
     */
    protected $appEnvironment;

    /**
     * @param AppEnvironment|string $environment
     * @param bool $debug
     */
    public function __construct($environment, $debug = null)
    {
        if (is_string($environment)) {
            $environment = AppEnvironment::fromString($environment);
        } elseif (!$environment instanceof AppEnvironment) {
            throw new \InvalidArgumentException(sprintf(
                'environment has to be an AppEnvironment, but %s given',
                is_object($environment) ? get_class($environment) : gettype($environment)
            ));
        }
        $this->appEnvironment = $environment;

        parent::__construct($environment->getEnvironment(), $debug);
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),

            new Mealz\MealBundle\MealzMealBundle(),
            new Mealz\UserBundle\MealzUserBundle(),
            new Mealz\AccountingBundle\MealzAccountingBundle(),
            new Mealz\TemplateBundle\MealzTemplateBundle(),
            new Mealz\RestBundle\MealzRestBundle(),
        );

        if (in_array($this->getEnvironment(), array('devbox', 'dev', 'deploy', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/' . $this->appEnvironment->getEnvironment() . '/config.yml');
    }

}
