<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to add/remove "deleted" column to "slot" table.
 */
final class Version20211118173321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add "deleted" column to "slot" table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE slot ADD deleted TINYINT(1) DEFAULT \'0\' NOT NULL AFTER disabled');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE slot DROP deleted');
    }
}
