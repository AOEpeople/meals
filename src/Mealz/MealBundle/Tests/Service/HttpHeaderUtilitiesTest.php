<?php


namespace Mealz\MealBundle\Tests\Service;

use Mealz\MealBundle\Service\HttpHeaderUtility;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Yaml\Yaml;

class HttpHeaderUtilitiesTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var HttpHeaderUtility
     */
    protected $httpHeaderUtilities;

    public function setUp()
    {
        $this->httpHeaderUtilities = new HttpHeaderUtility();
    }

    /**
     * @dataProvider provideDataForTestGetLocale
     * @param $acceptedLocales
     * @param $headerString
     * @param $expectedLocale
     */
    public function testGetLocale($acceptedLocales, $headerString, $expectedLocale)
    {
        $this->httpHeaderUtilities->setLocales($acceptedLocales);

        $this->assertSame(
            $expectedLocale,
            $this->httpHeaderUtilities->getLocaleFromAcceptLanguageHeader($headerString)
        );
    }

    public function provideDataForTestGetLocale()
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/getLocaleFromAcceptLanguageTestData.yml'));
    }
}
