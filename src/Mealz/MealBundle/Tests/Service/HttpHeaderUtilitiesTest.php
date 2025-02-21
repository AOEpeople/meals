<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Service\HttpHeaderUtility;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class HttpHeaderUtilitiesTest extends TestCase
{
    /**
     * @dataProvider provideDataForTestGetLocale
     */
    public function testGetLocale(array $acceptedLocales, ?string $headerString, string $expectedLocale): void
    {
        $httpHeaderUtility = new HttpHeaderUtility($acceptedLocales);

        $this->assertSame(
            $expectedLocale,
            $httpHeaderUtility->getLocaleFromAcceptLanguageHeader($headerString)
        );
    }

    public function provideDataForTestGetLocale()
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/getLocaleFromAcceptLanguageTestData.yml'));
    }
}
