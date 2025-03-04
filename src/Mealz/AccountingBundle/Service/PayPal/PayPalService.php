<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service\PayPal;

use App\Mealz\AccountingBundle\Service\PayPal\Order as PayPalOrder;
use DateTime;
use DateTimeInterface;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\Http\ApiResponse;
use PaypalServerSdkLib\Logging\LoggingConfigurationBuilder;
use PaypalServerSdkLib\Logging\RequestLoggingConfigurationBuilder;
use PaypalServerSdkLib\Logging\ResponseLoggingConfigurationBuilder;
use PaypalServerSdkLib\Models\Order;
use PaypalServerSdkLib\Models\PurchaseUnit;
use PaypalServerSdkLib\PaypalServerSdkClient;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use Psr\Log\LogLevel;
use RuntimeException;

class PayPalService
{
    private PaypalServerSdkClient $client;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $environment,
    ) {
        $this->client = PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init(
                    $clientId,
                    $clientSecret
                )
            )
            ->environment('prod' === $environment ? Environment::PRODUCTION : Environment::SANDBOX)
            ->loggingConfiguration(
                LoggingConfigurationBuilder::init()
                    ->level(LogLevel::INFO)
                    ->requestConfiguration(RequestLoggingConfigurationBuilder::init()->body(true))
                    ->responseConfiguration(ResponseLoggingConfigurationBuilder::init()->headers(true))
                    ->responseConfiguration(ResponseLoggingConfigurationBuilder::init()->body(true))
            )
            ->build();
    }

    public function getOrder(string $orderID): ?PayPalOrder
    {
        $response = $this->client->getOrdersController()->ordersGet(['id' => $orderID]);

        return $this->evaluateResponse($response);
    }

    /**
     * @throw RuntimeException
     */
    public function evaluateResponse(ApiResponse $response): ?PayPalOrder
    {
        return match ($response->getStatusCode()) {
            200 => $this->toPayPalOrder($response->getResult()),
            404 => null,
            default => throw new RuntimeException(
                sprintf(
                    'unexpected api response, status: %d, path: %s, method: %s',
                    $response->getStatusCode(),
                    $response->getRequest()->getQueryUrl(),
                    $response->getRequest()->getHttpMethod()
                ),
                1633425374
            ),
        };
    }

    private function toPayPalOrder(Order $orderResp): PayPalOrder
    {
        if (!property_exists($orderResp, 'id')) {
            throw new RuntimeException('invalid order response; order-id not found');
        }
        if (!property_exists($orderResp, 'status')) {
            throw new RuntimeException('invalid order response; order status not found');
        }
        if (!property_exists($orderResp, 'purchaseUnits')) {
            throw new RuntimeException('invalid order response; purchase units not found');
        }

        $grossAmount = $this->toOrderAmount($orderResp->getPurchaseUnits());

        // Get order date and time
        // Order response contains two date-time values, i.e. create and update.
        // Since we don't have any specific requirements regarding which date-time value to use,
        // we are simply using order update date-time.
        if (!property_exists($orderResp, 'updateTime')) {
            throw new RuntimeException('invalid order response; order date-time not found');
        }

        $orderDateTime = DateTime::createFromFormat(DateTimeInterface::ATOM, $orderResp->getUpdateTime());
        if (false === $orderDateTime) {
            throw new RuntimeException('invalid order response; invalid date-time format; expected 2021-10-01T08:51:14Z, got ' . $orderResp->getUpdateTime());
        }

        return new PayPalOrder($orderResp->getId(), $grossAmount, $orderDateTime, $orderResp->getStatus());
    }

    /**
     * @param PurchaseUnit[] $purchaseUnits
     *
     * @throws RuntimeException
     */
    private function toOrderAmount($purchaseUnits): float
    {
        if (!is_array($purchaseUnits) || !isset($purchaseUnits[0])) {
            throw new RuntimeException('invalid order response; purchase units not found');
        }
        if (!is_object($purchaseUnits[0]->getAmount())) {
            throw new RuntimeException('invalid order response; invalid amount type; expected object, got ' . gettype($purchaseUnits[0]->getAmount()));
        }
        if (!property_exists($purchaseUnits[0]->getAmount(), 'value')) {
            throw new RuntimeException('invalid order response; order amount not found');
        }

        return (float) $purchaseUnits[0]->getAmount()->getValue();
    }
}
