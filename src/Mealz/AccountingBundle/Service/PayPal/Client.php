<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service\PayPal;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalHttp\HttpException;
use PayPalHttp\HttpRequest;
use PayPalHttp\HttpResponse;
use PayPalHttp\IOException;
use RuntimeException;

class Client
{
    /**
     * PayPal SDK environments
     */
    private const SDK_ENV_SANDBOX = 'sandbox';
    private const SDK_ENV_PRODUCTION = 'production';

    private string $clientID;
    private string $clientSecret;
    private string $sdkEnv;

    private ?PayPalHttpClient $paypalHTTPClient;

    public function __construct(string $clientID, string $clientSecret, string $sdkEnv)
    {
        $this->clientID = $clientID;
        $this->clientSecret = $clientSecret;
        $this->validateSetSDKEnV($sdkEnv);

        $this->paypalHTTPClient = null;
    }

    private function validateSetSDKEnV(string $sdkEnv): void
    {
        if ((self::SDK_ENV_PRODUCTION !== $sdkEnv) && (self::SDK_ENV_SANDBOX !== $sdkEnv)) {
            throw new RuntimeException('invalid paypal sdk environment: '.$sdkEnv, 1633100141);
        }

        $this->sdkEnv = $sdkEnv;
    }

    /**
     * @throws HttpException
     * @throws IOException
     */
    public function execute(HttpRequest $request): HttpResponse
    {
        return $this->getPayPalHttpClient()->execute($request);
    }

    private function getPayPalHttpClient(): PayPalHttpClient
    {
        if (null !== $this->paypalHTTPClient) {
            return $this->paypalHTTPClient;
        }

        if (self::SDK_ENV_PRODUCTION === $this->sdkEnv) {
            $paypalSDKEnv = new ProductionEnvironment($this->clientID, $this->clientSecret);
        } else {
            $paypalSDKEnv = new SandboxEnvironment($this->clientID, $this->clientSecret);
        }

        $this->paypalHTTPClient = new PayPalHttpClient($paypalSDKEnv);

        return $this->paypalHTTPClient;
    }
}
