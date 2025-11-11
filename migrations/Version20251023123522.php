<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251023123522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profile ADD username VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11CCFA12B8');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11CCFA12B8
            FOREIGN KEY (profile_id)
            REFERENCES profile (id)
            ON UPDATE CASCADE
            ON DELETE RESTRICT'
        );
        $this->addSql('ALTER TABLE profile_role DROP FOREIGN KEY FK_E1A105FECCFA12B8');
        $this->addSql('ALTER TABLE profile_role ADD CONSTRAINT FK_E1A105FECCFA12B8
            FOREIGN KEY (profile_id)
            REFERENCES profile (id)
            ON UPDATE CASCADE
            ON DELETE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profile DROP username');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11CCFA12B8');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_E1A105FECCFA12B8');
        $this->addSql('ALTER TABLE profile_role ADD CONSTRAINT FK_E1A105FECCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE');
    }
}
