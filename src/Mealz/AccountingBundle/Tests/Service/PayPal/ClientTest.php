<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Service\PayPal;

use App\Mealz\AccountingBundle\Service\PayPal\Client;
use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ClientTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider validSDKEnv
     * @doesNotPerformAssertions
     */
    public function instantiationSuccess(string $sdkEnv): void
    {
        try {
            new Client('jon', 'doe', $sdkEnv);
        } catch (Exception $e) {
            $this->fail('expected no exception');
        }
    }

    public function validSDKEnv(): array
    {
        return [
            ['sandbox'],
            ['production'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider invalidSDKEnv
     */
    public function instantiationFailure(string $sdkEnv): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1633100141);
        $this->expectExceptionMessage('invalid paypal sdk environment: '.$sdkEnv);

        new Client('jon', 'doe', $sdkEnv);
    }

    public function invalidSDKEnv(): array
    {
        return [
            [''],
            ['anything-invalid'],
        ];
    }
}
