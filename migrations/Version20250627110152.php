<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250627110152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE day DROP event_id');
        $this->addSql('ALTER TABLE guest_invitation ADD eventParticipation INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participant CHANGE profile_id profile_id VARCHAR(255) NOT NULL, CHANGE confirmed confirmed TINYINT(1) DEFAULT 0 NOT NULL, CHANGE event event_participation INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant CHANGE profile_id profile_id VARCHAR(255) DEFAULT NULL, CHANGE confirmed confirmed TINYINT(1) NOT NULL, CHANGE event_participation event INT DEFAULT NULL');
        $this->addSql('ALTER TABLE day ADD event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE guest_invitation DROP eventParticipation');
    }
}
