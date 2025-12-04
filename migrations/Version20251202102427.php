<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202102427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Generate prices table';
    }

    public function up(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        $this->addSql('CREATE TABLE price (year INT NOT NULL, price NUMERIC(10, 4) NOT NULL, price_combined NUMERIC(10, 4) NOT NULL, INDEX year (year), PRIMARY KEY(year)) ENGINE = InnoDB;');

        $this->addSql('INSERT INTO price (year, price, price_combined) VALUES (2016, 3.20, 0.00);');
        $this->addSql('INSERT INTO price (year, price, price_combined) VALUES (2017, 3.20, 0.00);');
        $this->addSql('INSERT INTO price (year, price, price_combined) VALUES (2018, 3.40, 0.00);');
        $this->addSql('INSERT INTO price (year, price, price_combined) VALUES (2019, 3.40, 0.00);');
        $this->addSql('INSERT INTO price (year, price, price_combined) VALUES (2020, 3.40, 0.00);');
        $this->addSql('INSERT INTO price (year, price, price_combined) VALUES (2021, 3.40, 0.00);');
        $this->addSql('INSERT INTO price (year, price, price_combined) VALUES (2022, 3.60, 5.60);');
        $this->addSql('INSERT INTO price (year, price, price_combined) VALUES (2023, 3.60, 5.60);');
        $this->addSql('INSERT INTO price (year, price, price_combined) VALUES (2024, 4.13, 6.13);');
        $this->addSql('INSERT INTO price (year, price, price_combined) VALUES (2025, 4.40, 6.40);');
    }

    public function down(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        $this->addSql('DROP TABLE price');
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
