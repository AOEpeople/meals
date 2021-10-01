<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service\PayPal;

use App\Mealz\AccountingBundle\Service\PayPal\Client as PayPalClient;
use App\Mealz\AccountingBundle\Service\PayPal\Order as PayPalOrder;
use DateTime;
use DateTimeInterface;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpException;
use PayPalHttp\IOException;
use RuntimeException;

class PayPalService
{
    private PayPalClient $client;

    public function __construct(PayPalClient $client)
    {
        $this->client = $client;
    }

    /**
     * @throws HttpException
     * @throws IOException
     */
    public function getOrder(string $orderID): PayPalOrder
    {
        $request = new OrdersGetRequest($orderID);
        $response = $this->client->execute($request);

        if (200 !== $response->statusCode) {
            throw new RuntimeException(sprintf(
                'api request failure; response status: %d, path; %s, method: %s',
                $response->statusCode,
                $request->path,
                $request->verb
            ));
        }

        return $this->toPayPalOrder($response->result);
    }

    private function toPayPalOrder($orderResp): PayPalOrder
    {
        if (!is_object($orderResp)) {
            throw new RuntimeException('invalid order response, expected object, got '. gettype($orderResp));
        }
        if (!property_exists($orderResp, 'id')) {
            throw new RuntimeException('invalid order response; order-id not found');
        }
        if (!property_exists($orderResp, 'purchase_units')
            || !is_array($orderResp->purchase_units)
            || !isset($orderResp->purchase_units[0])) {
            throw new RuntimeException('invalid order response; purchase units not found');
        }
        if (!is_object($orderResp->purchase_units[0]->amount)) {
            throw new RuntimeException(
                'invalid order response; invalid amount type; expected object, got '
                . gettype($orderResp->purchase_units[0]->amount)
            );
        }
        if (!property_exists($orderResp->purchase_units[0]->amount, 'value')) {
            throw new RuntimeException('invalid order response; order amount not found');
        }

        $grossAmount = (float) $orderResp->purchase_units[0]->amount->value;

        // Get order date and time
        // Order response contains two date-time values, i.e. create and update.
        // Since we don't any specific requirements regarding which date-time value to use,
        // we are simply using order update date-time.
        if (!property_exists($orderResp, 'update_time')) {
            throw new RuntimeException('invalid order response; order date-time not found');
        }

        $orderDateTime = DateTime::createFromFormat(DateTimeInterface::ATOM, $orderResp->update_time);
        if (false === $orderDateTime) {
            throw new RuntimeException(
                'invalid order response; invalid date-time format; expected 2021-10-01T08:51:14Z, got '
                . $orderResp->update_time
            );
        }

        return new PayPalOrder($orderResp->id, $grossAmount, $orderDateTime);
    }
}
