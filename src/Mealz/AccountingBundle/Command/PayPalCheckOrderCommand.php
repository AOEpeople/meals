<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Command;

use App\Mealz\AccountingBundle\Service\PayPal\PayPalService;
use Exception;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PayPalCheckOrderCommand extends Command
{
    private PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        parent::__construct();

        $this->paypalService = $paypalService;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('meals:payment:check-paypal-order')
            ->setDescription('Get details of a meals payment done via PayPal.')
            ->addOption('order-id', 'o', InputOption::VALUE_REQUIRED, 'PayPal Order-ID.');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $orderID = $input->getOption('order-id');
        if (null === $orderID) {
            throw new RuntimeException('Missing required parameter: order-id');
        }

        try {
            $order = $this->paypalService->getOrder($orderID);
        } catch (Exception $e) {
            $output->writeln('Error getting transaction details: ' . $e->getMessage());

            return 1;
        }

        if (null === $order) {
            $output->writeln('Order not found');

            return 0;
        }

        $output->writeln([
            '',
            'Order-ID:' . $order->getId(),
            'Amount: ' . number_format($order->getAmount(), 2) . ' EUR',
            'Date: ' . $order->getDateTime()->format('Y-m-d H:i:s'),
            'Status: ' . $order->getStatus(),
            '',
        ]);

        return 0;
    }
}
