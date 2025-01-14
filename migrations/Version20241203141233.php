<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241203141233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B113BAE0AA7');
        $this->addSql('DROP INDEX IDX_D79F6B113BAE0AA7 ON participant');
        $this->addSql('ALTER TABLE participant CHANGE event event_participation INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B118F0C52E3 FOREIGN KEY (event_participation) REFERENCES event_participation (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B118F0C52E3 ON participant (event_participation)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B118F0C52E3');
        $this->addSql('DROP INDEX IDX_D79F6B118F0C52E3 ON participant');
        $this->addSql('ALTER TABLE participant CHANGE event_participation event INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B113BAE0AA7 FOREIGN KEY (event) REFERENCES event_participation (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B113BAE0AA7 ON participant (event)');
    }
}
