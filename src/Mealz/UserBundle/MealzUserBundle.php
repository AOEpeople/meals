<?php

namespace App\Mealz\UserBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use App\Mealz\UserBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

class MealzUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideServiceCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
