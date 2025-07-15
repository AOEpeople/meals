<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230706090606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        $this->addSql('ALTER TABLE profile ADD email VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        $this->addSql('ALTER TABLE profile DROP COLUMN email');
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
