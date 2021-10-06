<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Service\PayPal\Order;
use App\Mealz\AccountingBundle\Service\PayPal\PayPalService;
use App\Mealz\AccountingBundle\Service\Exception\BadDataException;
use App\Mealz\AccountingBundle\Service\Exception\ResourceNotFoundException;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JsonException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class TransactionService
{
    private const PAYMENT_METHOD_PAYPAL = '0';

    private const EXCEPTION_INVALID_ORDER_AMOUNT = 1633094204;
    private const EXCEPTION_ORDER_NOT_COMPLETED  = 1633519677;

    private PayPalService $paypalService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PayPalService $paypalService,
        EntityManagerInterface $entityManager
    ) {
        $this->paypalService = $paypalService;
        $this->entityManager = $entityManager;
    }

    public function createFromRequest(Request $request, Profile $profile): void
    {
        try {
            $transaction = $this->getTransactionData($request->getContent());
        } catch (BadDataException $e) {
            throw new BadRequestHttpException('', $e);
        }

        try {
            $this->create($transaction['orderID'], $transaction['amount'], $profile);
        } catch (ResourceNotFoundException $rnf) {
            throw new UnprocessableEntityHttpException('', $rnf, 1633509057);
        } catch (RuntimeException $rte) {
            $errCode = $rte->getCode();
            if (self::EXCEPTION_ORDER_NOT_COMPLETED === $errCode
                || self::EXCEPTION_INVALID_ORDER_AMOUNT === $errCode) {
                throw new UnprocessableEntityHttpException('', $rte, 1633513408);
            }

            throw $rte;
        }
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function create(string $orderID, float $amount, Profile $profile): void
    {
        try {
            $order = $this->paypalService->getOrder($orderID);
        } catch (Exception $e) {
            throw new RuntimeException('get order error, order-id: '.$orderID, 1633425633, $e);
        }

        if (null === $order) {
            throw new ResourceNotFoundException('order-id: '.$orderID);
        }

        $this->validateOrder($order, $amount);

        $transaction = new Transaction();
        $transaction->setProfile($profile);
        $transaction->setOrderId($order->getId());
        $transaction->setAmount($order->getAmount());
        $transaction->setDate($order->getDateTime());
        $transaction->setPaymethod(self::PAYMENT_METHOD_PAYPAL);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }

    private function validateOrder(Order $order, float $amount): void
    {
        if (!$order->isCompleted()) {
            throw new RuntimeException(
                'order not completed, order-id: '.$order->getId(),
                self::EXCEPTION_ORDER_NOT_COMPLETED
            );
        }

        if ($amount !== $order->getAmount()) {
            throw new RuntimeException(
                sprintf(
                    'order amount mismatch, expected: %f, got: %f, order-id: %s',
                    $order->getAmount(), $amount, $order->getId()
                ),
                self::EXCEPTION_INVALID_ORDER_AMOUNT
            );
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws BadDataException
     */
    private function getTransactionData($jsonPayload): array
    {
        $data = [];
        $payload = $this->jsonDecode($jsonPayload);

        foreach ($payload as $item) {
            if (!is_array($item) || !isset($item['name'], $item['value'])) {
                throw new BadDataException('expected array with name, value keys, got '.print_r($item, true), 1633436079);
            }
            $data[$item['name']] = $item['value'];
        }

        if (!isset($data['ecash[orderid]']) || ('' === $data['ecash[orderid]'])) {
            throw new BadDataException('missing or invalid order-id', 1633095392);
        }
        if (!isset($data['ecash[amount]']) || (0.0 >= (float) $data['ecash[amount]'])) {
            throw new BadDataException('invalid amount', 1633095450);
        }

        return [
            'orderID'   => $data['ecash[orderid]'],
            'amount'    => (float) $data['ecash[amount]'],
        ];
    }

    /**
     * @param mixed $json
     *
     * @return array<string, mixed>
     *
     * @throws BadDataException
     */
    private function jsonDecode($json): array
    {
        if (!is_string($json) || '' === $json) {
            return [];
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new BadDataException('json decode error', 1633509529, $e);
        }

        return $data;
    }
}
