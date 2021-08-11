<?php

namespace App\Mealz\AccountingBundle\Controller\Payment;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

class PaypalClient
{
    private static $clientId = null;
    private static $clientSecret = null;
    private static $environment = null;

    /**
     * Returns PayPal HTTP client instance with environment that has access
     * credentials context. Use this instance to invoke PayPal APIs, provided the
     * credentials have access.
     */
    public static function client()
    {
        return new PayPalHttpClient(self::environment());
    }

    /**
     * Set up and return PayPal PHP SDK environment with PayPal access credentials.
     * This sample uses SandboxEnvironment. In production, use LiveEnvironment.
     */
    public static function environment()
    {
        $clientId = getenv("CLIENT_ID") ?: self::$clientId;
        $clientSecret = getenv("CLIENT_SECRET") ?: self::$clientSecret;

        if (self::$environment === 'production') {
            return new ProductionEnvironment($clientId, $clientSecret);
        }

        return new SandboxEnvironment($clientId, $clientSecret);
    }

    /**
     * Set the PayPal client credentials to be used in the environment() function.
     * @param $identifier
     * @param $secret
     * @param $environment
     */
    public static function setCredentialsAndEnvironment($identifier, $secret, $environment)
    {
        self::$clientId = $identifier;
        self::$clientSecret = $secret;
        self::$environment = $environment;
    }
}
