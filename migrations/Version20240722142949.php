<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240722142949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE day DROP event_id');
        $this->addSql('ALTER TABLE event_participation DROP INDEX UNIQ_8F0C52E3E5A02990');
        $this->addSql('CREATE UNIQUE INDEX unique_day_event ON event_participation (day, event)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE day ADD event_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX unique_day_event ON event_participation');
    }
}
