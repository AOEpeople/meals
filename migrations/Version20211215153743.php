<?php

declare(strict_types=1);

namespace DoctrineMigrations;

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
        $this->addSql('CREATE TABLE participant_dish (participant_id INT NOT NULL, dish_id INT NOT NULL, INDEX IDX_41C99E869D1C3019 (participant_id), INDEX IDX_41C99E86148EB0CB (dish_id), PRIMARY KEY(participant_id, dish_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE participant_dish');
    }
}
