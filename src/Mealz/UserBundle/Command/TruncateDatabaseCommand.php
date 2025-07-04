<?php

namespace App\Mealz\UserBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mealz:database:truncate')]
class TruncateDatabaseCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private Connection $connection)
    {
        parent::__construct();
    }

    /**
     * Summary of execute.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->em->getMetadataFactory()->getAllMetadata() as $metadata) {
            try {
                $tableName = $metadata->getTableName();
                $sql = $platform->getTruncateTableSQL($tableName, true);
                $this->connection->executeStatement($sql);
                $output->writeln("Truncated: $tableName");
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
            }
        }

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
        $output->writeln('<info>All tables truncated.</info>');

        return Command::SUCCESS;
    }
}
