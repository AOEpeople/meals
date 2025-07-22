<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250722142805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE profile_role_orig');
        $this->addSql('DROP TABLE profile_orig');
        $this->addSql('ALTER TABLE day ADD CONSTRAINT FK_E5A0299071F7E88B FOREIGN KEY (event_id) REFERENCES event_participation (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E5A0299071F7E88B ON day (event_id)');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_8F0C52E3E5A02990 FOREIGN KEY (day) REFERENCES day (id)');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_8F0C52E33BAE0AA7 FOREIGN KEY (event) REFERENCES event (id)');
        $this->addSql('ALTER TABLE guest_invitation ADD CONSTRAINT FK_CC0531331FB8D185 FOREIGN KEY (host_id) REFERENCES profile (id) ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE login CHANGE password password VARCHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE login ADD CONSTRAINT FK_AA08CB10CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE participant CHANGE profile_id profile_id VARCHAR(255) NOT NULL, CHANGE confirmed confirmed TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B113BAE0AA7 FOREIGN KEY (event) REFERENCES event_participation (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B113BAE0AA7 ON participant (event)');
        $this->addSql('ALTER TABLE participant_dish ADD CONSTRAINT FK_41C99E869D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participant_dish ADD CONSTRAINT FK_41C99E86148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile_role ADD CONSTRAINT FK_E1A105FECCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D18157AA0F FOREIGN KEY (profile) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE session CHANGE sess_data sess_data LONGBLOB NOT NULL');
        $this->addSql('ALTER TABLE session RENAME INDEX session_sess_lifetime_idx TO sess_lifetime_idx');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE profile_role_orig (profile_id VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, role_id INT NOT NULL) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE profile_orig (id VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, firstName VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, company VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, settlementHash VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B113BAE0AA7');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11CCFA12B8');
        $this->addSql('DROP INDEX IDX_D79F6B113BAE0AA7 ON participant');
        $this->addSql('ALTER TABLE participant CHANGE profile_id profile_id VARCHAR(255) DEFAULT NULL, CHANGE confirmed confirmed TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE profile_role DROP FOREIGN KEY FK_E1A105FECCFA12B8');
        $this->addSql('ALTER TABLE participant_dish DROP FOREIGN KEY FK_41C99E869D1C3019');
        $this->addSql('ALTER TABLE participant_dish DROP FOREIGN KEY FK_41C99E86148EB0CB');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D18157AA0F');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_8F0C52E3E5A02990');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_8F0C52E33BAE0AA7');
        $this->addSql('ALTER TABLE day DROP FOREIGN KEY FK_E5A0299071F7E88B');
        $this->addSql('DROP INDEX UNIQ_E5A0299071F7E88B ON day');
        $this->addSql('ALTER TABLE login DROP FOREIGN KEY FK_AA08CB10CCFA12B8');
        $this->addSql('ALTER TABLE login CHANGE password password VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE guest_invitation DROP FOREIGN KEY FK_CC0531331FB8D185');
        $this->addSql('ALTER TABLE session CHANGE sess_data sess_data BLOB NOT NULL');
        $this->addSql('ALTER TABLE session RENAME INDEX sess_lifetime_idx TO session_sess_lifetime_idx');
    }
}
