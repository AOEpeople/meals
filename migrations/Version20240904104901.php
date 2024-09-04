<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240904104901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE day ADD CONSTRAINT FK_E5A02990C86F3B2F FOREIGN KEY (week_id) REFERENCES week (id)');
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB812469DE2 FOREIGN KEY (category_id) REFERENCES Category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB8727ACA70 FOREIGN KEY (parent_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX unique_day_event ON event_participation');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_8F0C52E3E5A02990 FOREIGN KEY (day) REFERENCES day (id)');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_8F0C52E33BAE0AA7 FOREIGN KEY (event) REFERENCES event (id)');
        $this->addSql('CREATE INDEX IDX_8F0C52E3E5A02990 ON event_participation (day)');
        $this->addSql('ALTER TABLE guest_invitation ADD eventParticipation INT NOT NULL');
        $this->addSql('ALTER TABLE guest_invitation ADD CONSTRAINT FK_CC0531331FB8D185 FOREIGN KEY (host_id) REFERENCES profile (id) ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE guest_invitation ADD CONSTRAINT FK_CC05313366C8FC2F FOREIGN KEY (meal_day_id) REFERENCES day (id) ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE guest_invitation ADD CONSTRAINT FK_CC053133AF63C1E0 FOREIGN KEY (eventParticipation) REFERENCES event_participation (id) ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_CC053133AF63C1E0 ON guest_invitation (eventParticipation)');
        $this->addSql('ALTER TABLE login ADD CONSTRAINT FK_AA08CB10CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE meal ADD CONSTRAINT FK_9EF68E9C148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id)');
        $this->addSql('ALTER TABLE meal ADD CONSTRAINT FK_9EF68E9CE5A02990 FOREIGN KEY (day) REFERENCES day (id)');
        $this->addSql('ALTER TABLE participant CHANGE profile_id profile_id VARCHAR(255) NOT NULL, CHANGE confirmed confirmed TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B113BAE0AA7 FOREIGN KEY (event) REFERENCES event_participation (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11639666D6 FOREIGN KEY (meal_id) REFERENCES meal (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B113BAE0AA7 ON participant (event)');
        $this->addSql('ALTER TABLE participant_dish ADD CONSTRAINT FK_41C99E869D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participant_dish ADD CONSTRAINT FK_41C99E86148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile_role ADD CONSTRAINT FK_E1A105FECCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile_role ADD CONSTRAINT FK_E1A105FED60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D18157AA0F FOREIGN KEY (profile) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE session CHANGE sess_data sess_data LONGBLOB NOT NULL');
        $this->addSql('ALTER TABLE session RENAME INDEX session_sess_lifetime_idx TO sess_lifetime_idx');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meal DROP FOREIGN KEY FK_9EF68E9C148EB0CB');
        $this->addSql('ALTER TABLE meal DROP FOREIGN KEY FK_9EF68E9CE5A02990');
        $this->addSql('ALTER TABLE login DROP FOREIGN KEY FK_AA08CB10CCFA12B8');
        $this->addSql('ALTER TABLE participant_dish DROP FOREIGN KEY FK_41C99E869D1C3019');
        $this->addSql('ALTER TABLE participant_dish DROP FOREIGN KEY FK_41C99E86148EB0CB');
        $this->addSql('ALTER TABLE session CHANGE sess_data sess_data BLOB NOT NULL');
        $this->addSql('ALTER TABLE session RENAME INDEX sess_lifetime_idx TO session_sess_lifetime_idx');
        $this->addSql('ALTER TABLE profile_role DROP FOREIGN KEY FK_E1A105FECCFA12B8');
        $this->addSql('ALTER TABLE profile_role DROP FOREIGN KEY FK_E1A105FED60322AC');
        $this->addSql('ALTER TABLE guest_invitation DROP FOREIGN KEY FK_CC0531331FB8D185');
        $this->addSql('ALTER TABLE guest_invitation DROP FOREIGN KEY FK_CC05313366C8FC2F');
        $this->addSql('ALTER TABLE guest_invitation DROP FOREIGN KEY FK_CC053133AF63C1E0');
        $this->addSql('DROP INDEX IDX_CC053133AF63C1E0 ON guest_invitation');
        $this->addSql('ALTER TABLE guest_invitation DROP eventParticipation');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B113BAE0AA7');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11639666D6');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11CCFA12B8');
        $this->addSql('DROP INDEX IDX_D79F6B113BAE0AA7 ON participant');
        $this->addSql('ALTER TABLE participant CHANGE profile_id profile_id VARCHAR(255) DEFAULT NULL, CHANGE confirmed confirmed TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE day DROP FOREIGN KEY FK_E5A02990C86F3B2F');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D18157AA0F');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_8F0C52E3E5A02990');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_8F0C52E33BAE0AA7');
        $this->addSql('DROP INDEX IDX_8F0C52E3E5A02990 ON event_participation');
        $this->addSql('CREATE UNIQUE INDEX unique_day_event ON event_participation (day, event)');
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB812469DE2');
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB8727ACA70');
    }
}
