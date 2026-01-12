<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251209101903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove price column in dish and meal table.';
    }

    public function up(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();
        $this->addSql('ALTER TABLE dish DROP COLUMN price;');
        $this->addSql('ALTER TABLE meal DROP COLUMN price;');
    }

    public function down(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();
        $this->addSql('ALTER TABLE dish ADD COLUMN price NUMERIC(10, 4);');
        $this->addSql('ALTER TABLE meal ADD COLUMN price NUMERIC(10, 4);');
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
