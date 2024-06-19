<?php

namespace App\Mealz;

use Closure;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class AuthenticationEnvVarProcessor implements EnvVarProcessorInterface
{
    /**
     * @psalm-return 'PUBLIC_ACCESS'|'ROLE_USER'|null
     */
    public function getEnv($prefix, $name, Closure $getEnv): ?string
    {
        if ('auth-mode' !== $prefix) {
            return null;
        }

        $env = $getEnv($name);

        return match ($env) {
            'oauth' => 'ROLE_USER',
            default => 'PUBLIC_ACCESS',
        };
    }

    /**
     * @return string[]
     *
     * @psalm-return array{'auth-mode': 'string'}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'auth-mode' => 'string',
        ];
    }
}
