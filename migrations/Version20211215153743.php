<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to add/remove table which connects participants, who booked a combined meal, with their chosen dishes.
 */
final class Version20211215153743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add/remove table which connects participants, who booked a combined meal, with their chosen dishes.';
    }

    public function up(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        $this->addSql('CREATE TABLE participant_dish (participant_id INT NOT NULL, dish_id INT NOT NULL, INDEX IDX_41C99E869D1C3019 (participant_id), INDEX IDX_41C99E86148EB0CB (dish_id), PRIMARY KEY(participant_id, dish_id)) ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        $this->addSql('DROP TABLE participant_dish');
    }

    /**
     * @throws Exception
     */
    function abortOnIncompatibleDB(): void
    {
        $currPlatform = $this->connection->getDatabasePlatform();
        $this->abortIf(
            !($currPlatform instanceof MySQLPlatform || $currPlatform instanceof MariaDBPlatform),
            'Migration can only be executed safely on \'mysql\' or \'maria-db\'.'
        );
    }
}
