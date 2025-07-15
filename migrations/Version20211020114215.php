<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Generate meal slot table and associate it with participant table.
 */
final class Version20211020114215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add meal "slot" table and associate it with "participant" table.';
    }

    public function up(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        $this->addSql(
            'CREATE TABLE slot ('.
            '`id` INT AUTO_INCREMENT NOT NULL, '.
            '`title` VARCHAR(255) NOT NULL, '.
            '`limit` INT UNSIGNED DEFAULT 0 NOT NULL, '.
            '`order` INT DEFAULT 0 NOT NULL, '.
            '`disabled` TINYINT(1) DEFAULT \'0\' NOT NULL, '.
            '`slug` VARCHAR(128) NOT NULL, '.
            'UNIQUE INDEX UNIQ_AC0E2067989D9B62 (slug), '.
            'PRIMARY KEY(id)'.
            ') ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE participant ADD slot_id INT DEFAULT NULL AFTER meal_id');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B1159E5119C FOREIGN KEY (slot_id) REFERENCES slot (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B1159E5119C ON participant (slot_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B1159E5119C');
        $this->addSql('DROP TABLE slot');
        $this->addSql('DROP INDEX IDX_D79F6B1159E5119C ON participant');
        $this->addSql('ALTER TABLE participant DROP COLUMN slot_id');
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
