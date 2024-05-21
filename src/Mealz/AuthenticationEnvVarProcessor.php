<?php

namespace App\Mealz;

use Closure;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class AuthenticationEnvVarProcessor implements EnvVarProcessorInterface
{
    public function getEnv($prefix, $name, Closure $getEnv): ?string
    {
        if ('auth-mode' !== $prefix) {
            return null;
        }

        $env = $getEnv($name);

        return match ($env) {
            'oauth' => 'ROLE_USER',
            default => 'IS_AUTHENTICATED_ANONYMOUSLY',
        };
    }

    public static function getProvidedTypes(): array
    {
        return [
            'auth-mode' => 'string',
        ];
    }
}
