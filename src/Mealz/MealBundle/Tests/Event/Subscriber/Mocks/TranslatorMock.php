<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Event\Subscriber\Mocks;

use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslatorMock implements TranslatorInterface
{
    public array $transCalls = [];
    public ?string $outputTransValue;
    public string $locale = 'en';

    public function trans(
        string $id,
        array $parameters = [],
        ?string $domain = null,
        ?string $locale = null
    ): string {
        $this->transCalls[] = [
            'id'        => $id,
            'parameters'=> $parameters,
            'domain'    => $domain,
            'locale'    => $locale,
        ];

        return $this->outputTransValue;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
