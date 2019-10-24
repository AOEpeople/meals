<?php

namespace Mealz\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $userProviderDef = $container->getDefinition('security.user.provider.ldap');
        $userProviderDef->setClass('Mealz\UserBundle\Provider\LdapUserProvider');

        $authProviderDef = $container->getDefinition('security.authentication.provider.ldap_bind');
        $authProviderDef->setClass('Mealz\UserBundle\Authentication\LdapBindAuthenticationProviderCustom');

        /*
         * Can't extend Symfony's ldap factory.
         * Original factory can't be removed from security bundle UserProviderFactory list.
         * @see Symfony\Bundle\SecurityBundle\SecurityBundle
         */
        $userProviderDef->addArgument(new Reference('mealz_user.post_login'));
    }
}
