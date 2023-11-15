<?php

namespace App\Mealz;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class AuthenticationEnvVarProcessor implements EnvVarProcessorInterface
{
    public function getEnv($prefix, $name, \Closure $getEnv)
    {
        if ('auth-mode' !== $prefix) {
            return;
        }

        $env = $getEnv($name);

        switch($env) {
            case 'oauth':
                return 'ROLE_USER';
            default:
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
