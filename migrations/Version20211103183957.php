<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211103183957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a hidden flag to the user profile.';
    }

    public function up(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        // Add the hidden flag from user profile
        $this->addSql('ALTER TABLE profile ADD hidden TINYINT(1) NOT NULL DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        // Drop the hidden flag from user profile
        $this->addSql('ALTER TABLE profile DROP COLUMN hidden');
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
