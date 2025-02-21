<?php

namespace App\Mealz;

use Closure;
use Override;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

final class AuthenticationEnvVarProcessor implements EnvVarProcessorInterface
{
    /**
     * @psalm-return 'PUBLIC_ACCESS'|'ROLE_USER'|null
     */
    #[Override]
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
    #[Override]
    public static function getProvidedTypes(): array
    {
        return [
            'auth-mode' => 'string',
        ];
    }
}
