<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service\PayPal;

use App\Mealz\AccountingBundle\Service\PayPal\Order as PayPalOrder;
use DateTime;
use DateTimeInterface;
use Exception;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpRequest;
use PayPalHttp\HttpResponse;
use RuntimeException;

class PayPalService
{
    private PayPalHttpClient $client;

    public function __construct(PayPalHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @throw RuntimeException
     */
    public function getOrder(string $orderID): ?PayPalOrder
    {
        $request = new OrdersGetRequest($orderID);
        $response = $this->sendRequest($request);

        return match ($response->statusCode) {
            200 => $this->toPayPalOrder($response->result),
            404 => null,
            default => throw new RuntimeException(
                sprintf('unexpected api response, status: %d, path: %s, method: %s', $response->statusCode, $request->path, $request->verb),
                1633425374
            ),
        };
    }

    /**
     * @throws RuntimeException
     */
    private function sendRequest(HttpRequest $request): HttpResponse
    {
        try {
            return $this->client->execute($request);
        } catch (Exception $e) {
            throw new RuntimeException(
                sprintf('api request error; path: %s, method: %s', $request->path, $request->verb),
                1633507746,
                $e
            );
        }
    }

    /**
     * @param array|object|string $orderResp
     */
    private function toPayPalOrder(array|string|object $orderResp): PayPalOrder
    {
        if (!is_object($orderResp)) {
            throw new RuntimeException('invalid order response, expected object, got ' . gettype($orderResp));
        }
        if (!property_exists($orderResp, 'id')) {
            throw new RuntimeException('invalid order response; order-id not found');
        }
        if (!property_exists($orderResp, 'status')) {
            throw new RuntimeException('invalid order response; order status not found');
        }
        if (!property_exists($orderResp, 'purchase_units')) {
            throw new RuntimeException('invalid order response; purchase units not found');
        }

        $grossAmount = $this->toOrderAmount($orderResp->purchase_units);

        // Get order date and time
        // Order response contains two date-time values, i.e. create and update.
        // Since we don't have any specific requirements regarding which date-time value to use,
        // we are simply using order update date-time.
        if (!property_exists($orderResp, 'update_time')) {
            throw new RuntimeException('invalid order response; order date-time not found');
        }

        $orderDateTime = DateTime::createFromFormat(DateTimeInterface::ATOM, $orderResp->update_time);
        if (false === $orderDateTime) {
            throw new RuntimeException('invalid order response; invalid date-time format; expected 2021-10-01T08:51:14Z, got ' . $orderResp->update_time);
        }

        return new PayPalOrder($orderResp->id, $grossAmount, $orderDateTime, $orderResp->status);
    }

    private function toOrderAmount($purchaseUnits): float
    {
        if (!is_array($purchaseUnits) || !isset($purchaseUnits[0])) {
            throw new RuntimeException('invalid order response; purchase units not found');
        }
        if (!is_object($purchaseUnits[0]->amount)) {
            throw new RuntimeException('invalid order response; invalid amount type; expected object, got ' . gettype($purchaseUnits[0]->amount));
        }
        if (!property_exists($purchaseUnits[0]->amount, 'value')) {
            throw new RuntimeException('invalid order response; order amount not found');
        }

        return (float) $purchaseUnits[0]->amount->value;
    }
}
