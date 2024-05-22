<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Command;

use App\Mealz\AccountingBundle\Service\TransactionService;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use Exception;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mealz:accounting:paypal:add')]
class PayPalAddTransCommand extends Command
{
    private ProfileRepositoryInterface $profileRepo;
    private TransactionService $transService;

    public function __construct(ProfileRepositoryInterface $profileRepo, TransactionService $transService)
    {
        parent::__construct();

        $this->profileRepo = $profileRepo;
        $this->transService = $transService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a transaction for meals payment done via PayPal.')
            ->addOption('order-id', 'o', InputOption::VALUE_REQUIRED, 'PayPal Order-ID.')
            ->addOption('user-id', 'u', InputOption::VALUE_REQUIRED, 'User-ID.');
    }

    /**
     * @return int
     *
     * @psalm-return 0|1
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $profileID = $input->getOption('user-id');
        if (null === $profileID) {
            throw new RuntimeException('Missing required parameter: user-id');
        }

        $profile = $this->profileRepo->find($profileID);
        if (null === $profile) {
            $output->writeln('User not found');

            return 1;
        }

        $orderID = $input->getOption('order-id');
        if (null === $orderID) {
            throw new RuntimeException('Missing required parameter: order-id');
        }

        try {
            $this->transService->create($orderID, $profile);
        } catch (Exception $e) {
            $output->writeln('transaction create error: ' . $e->getMessage());

            return 1;
        }

        $output->writeln('Transaction created successfully.');

        return 0;
    }
}
