<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250724065316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE day DROP FOREIGN KEY FK_E5A0299071F7E88B');
        $this->addSql('DROP INDEX UNIQ_E5A0299071F7E88B ON day');
        $this->addSql('ALTER TABLE day DROP event_id');
        $this->addSql('ALTER TABLE event_participation DROP INDEX UNIQ_8F0C52E3E5A02990, ADD INDEX IDX_8F0C52E3E5A02990 (day)');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_8F0C52E33BAE0AA7');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_8F0C52E3E5A02990');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_8F0C52E33BAE0AA7 FOREIGN KEY (event) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_8F0C52E3E5A02990 FOREIGN KEY (day) REFERENCES day (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guest_invitation ADD eventParticipation INT DEFAULT NULL');
        $this->addSql('ALTER TABLE guest_invitation ADD CONSTRAINT FK_CC053133AF63C1E0 FOREIGN KEY (eventParticipation) REFERENCES event_participation (id) ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_CC053133AF63C1E0 ON guest_invitation (eventParticipation)');
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
        $this->addSql('ALTER TABLE event_participation DROP INDEX IDX_8F0C52E3E5A02990, ADD UNIQUE INDEX UNIQ_8F0C52E3E5A02990 (day)');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_8F0C52E3E5A02990');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_8F0C52E33BAE0AA7');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_8F0C52E3E5A02990 FOREIGN KEY (day) REFERENCES day (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_8F0C52E33BAE0AA7 FOREIGN KEY (event) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE day ADD event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE day ADD CONSTRAINT FK_E5A0299071F7E88B FOREIGN KEY (event_id) REFERENCES event_participation (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E5A0299071F7E88B ON day (event_id)');
        $this->addSql('ALTER TABLE guest_invitation DROP FOREIGN KEY FK_CC053133AF63C1E0');
        $this->addSql('DROP INDEX IDX_CC053133AF63C1E0 ON guest_invitation');
        $this->addSql('ALTER TABLE guest_invitation DROP eventParticipation');
    }
}
