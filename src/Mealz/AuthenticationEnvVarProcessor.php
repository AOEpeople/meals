<?php

namespace App\Mealz;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class AuthenticationEnvVarProcessor implements EnvVarProcessorInterface
{
    public function getEnv($prefix, $name, \Closure $getEnv)
    {
        $env = $getEnv($name);

        if ('auth-mode' === $prefix && 'oauth' === $env) {
            return 'ROLE_USER';
        } else if ('auth-mode' === $prefix) {
            return 'IS_AUTHENTICATED_ANONYMOUSLY';
        }
    }

    public static function getProvidedTypes()
    {
        return [
            'auth-mode' => 'string',
        ];
    }
}