<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251209102354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add price reference between meal and price table.';
    }

    public function up(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        $this->addSql('ALTER TABLE meal ADD COLUMN price_id INT NOT NULL;');
        $this->addSql('UPDATE meal SET price_id = YEAR(dateTime);');
        $this->addSql('ALTER TABLE meal ADD CONSTRAINT fk_meal_price FOREIGN KEY (price_id) REFERENCES price(year) ON DELETE CASCADE;');
    }

    public function down(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        $this->addSql('ALTER TABLE meal DROP FOREIGN KEY fk_meal_price;');
        $this->addSql('ALTER TABLE meal DROP COLUMN price_id;');
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
