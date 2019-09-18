<?php

namespace Mealz\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $userProviderDefinition = $container->getDefinition('security.user.provider.ldap');
        $userProviderDefinition->setClass('Mealz\UserBundle\Provider\LdapUserProvider');

        $authenticationProvider = $container->getDefinition('security.authentication.provider.ldap_bind');
        $authenticationProvider->setClass('Mealz\UserBundle\Authentication\LdapBindAuthenticationProviderCustom');

        /*
         * Can't extend Symfony's ldap factory.
         * Original factory can't be removed from security bundle UserProviderFactory list.
         * @see Symfony\Bundle\SecurityBundle\SecurityBundle
         */
        $userProviderDefinition->addArgument(new Reference('mealz_user.post_login'));
    }
}
