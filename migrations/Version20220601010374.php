<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to add/remove "one_serving_size" column from/to "dish" table.
 */
final class Version20220601010374 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add "one_serving_size" boolean column to "dish" table. '
                . 'The dish serving size is fixed and can not be changed if this flag is set.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dish ADD one_serving_size TINYINT(1) DEFAULT \'0\' NOT NULL AFTER enabled');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dish DROP one_serving_size');
    }
}
