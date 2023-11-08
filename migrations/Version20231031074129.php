<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231031074129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, deleted TINYINT(1) DEFAULT 0 NOT NULL, slug VARCHAR(128) NOT NULL, public TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_3BAE0AA7989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_participation (id INT AUTO_INCREMENT NOT NULL, day INT DEFAULT NULL, event INT DEFAULT NULL, INDEX IDX_8F0C52E3E5A02990 (day), INDEX IDX_8F0C52E33BAE0AA7 (event), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_8F0C52E3E5A02990 FOREIGN KEY (day) REFERENCES day (id)');
        $this->addSql('ALTER TABLE event_participation ADD CONSTRAINT FK_8F0C52E33BAE0AA7 FOREIGN KEY (event) REFERENCES event (id)');
        $this->addSql('ALTER TABLE day ADD event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE day ADD CONSTRAINT FK_E5A0299071F7E88B FOREIGN KEY (event_id) REFERENCES event_participation (id)');
        $this->addSql('CREATE INDEX IDX_E5A0299071F7E88B ON day (event_id)');
        $this->addSql('ALTER TABLE participant ADD event INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B113BAE0AA7 FOREIGN KEY (event) REFERENCES event_participation (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B113BAE0AA7 ON participant (event)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE day DROP FOREIGN KEY FK_E5A0299071F7E88B');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B113BAE0AA7');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_8F0C52E3E5A02990');
        $this->addSql('ALTER TABLE event_participation DROP FOREIGN KEY FK_8F0C52E33BAE0AA7');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_participation');
        $this->addSql('DROP INDEX IDX_E5A0299071F7E88B ON day');
        $this->addSql('ALTER TABLE day DROP event_id');
        $this->addSql('DROP INDEX IDX_D79F6B113BAE0AA7 ON participant');
        $this->addSql('ALTER TABLE participant DROP event');
    }
}
