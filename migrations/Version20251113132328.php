<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113132328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profile ADD username VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE profile ADD ssoId VARCHAR(255) NOT NULL');

        $this->addSql('UPDATE profile SET username=id WHERE 1=1');

        $this->addSql('ALTER TABLE login DROP FOREIGN KEY FK_AA08CB10CCFA12B8');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11CCFA12B8');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D18157AA0F');
        $this->addSql('ALTER TABLE profile_role DROP FOREIGN KEY FK_E1A105FECCFA12B8');
        $this->addSql('ALTER TABLE guest_invitation DROP FOREIGN KEY FK_CC0531331FB8D185');

        $this->addSql('ALTER TABLE profile DROP COLUMN id');
        $this->addSql('ALTER TABLE profile ADD COLUMN id INT NOT NULL');
        $this->addSql('ALTER TABLE profile ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE profile CHANGE id id INT NOT NULL AUTO_INCREMENT FIRST');

        $this->addSql('UPDATE guest_invitation gi INNER JOIN profile p ON gi.host_id = p.username SET gi.host_id=p.id WHERE 1=1');
        $this->addSql('UPDATE login l INNER JOIN profile p ON l.profile_id = p.username SET l.profile_id=p.id WHERE 1=1');
        $this->addSql('UPDATE participant pa INNER JOIN profile p ON pa.profile_id = p.username SET pa.profile_id=p.id WHERE 1=1');
        $this->addSql('UPDATE transaction t INNER JOIN profile p ON t.profile = p.username SET t.profile=p.id WHERE 1=1');
        $this->addSql('UPDATE profile_role pr INNER JOIN profile p ON pr.profile_id = p.username SET pr.profile_id=p.id WHERE 1=1');

        $this->addSql('ALTER TABLE guest_invitation CHANGE host_id host_id INT NOT NULL');
        $this->addSql('ALTER TABLE guest_invitation ADD CONSTRAINT FK_CC0531331FB8D185 FOREIGN KEY (host_id) REFERENCES profile (id) ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE login CHANGE profile_id profile_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE login ADD CONSTRAINT FK_AA08CB10CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE participant CHANGE profile_id profile_id INT NOT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE profile_role CHANGE profile_id profile_id INT NOT NULL');
        $this->addSql('ALTER TABLE profile_role ADD CONSTRAINT FK_E1A105FECCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transaction CHANGE profile profile INT NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D18157AA0F FOREIGN KEY (profile) REFERENCES profile (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guest_invitation DROP FOREIGN KEY FK_CC0531331FB8D185');
        $this->addSql('ALTER TABLE login DROP FOREIGN KEY FK_AA08CB10CCFA12B8');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11CCFA12B8');
        $this->addSql('ALTER TABLE profile_role DROP FOREIGN KEY FK_E1A105FECCFA12B8');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D18157AA0F');

        $this->addSql('ALTER TABLE profile DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE profile DROP COLUMN id');
        $this->addSql('ALTER TABLE profile ADD id VARCHAR(255) NOT NULL');

        $this->addSql('UPDATE profile SET id = username');

        $this->addSql('ALTER TABLE profile DROP COLUMN username');
        $this->addSql('ALTER TABLE profile DROP COLUMN ssoId');

        $this->addSql('ALTER TABLE guest_invitation CHANGE host_id host_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE login CHANGE profile_id profile_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE participant CHANGE profile_id profile_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE profile_role CHANGE profile_id profile_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE transaction CHANGE profile profile VARCHAR(255) NOT NULL');

        $this->addSql('UPDATE guest_invitation gi INNER JOIN profile p ON gi.host_id = p.id SET gi.host_id = p.id');
        $this->addSql('UPDATE login l INNER JOIN profile p ON l.profile_id = p.id SET l.profile_id = p.id');
        $this->addSql('UPDATE participant pa INNER JOIN profile p ON pa.profile_id = p.id SET pa.profile_id = p.id');
        $this->addSql('UPDATE transaction t INNER JOIN profile p ON t.profile = p.id SET t.profile = p.id');
        $this->addSql('UPDATE profile_role pr INNER JOIN profile p ON pr.profile_id = p.id SET pr.profile_id = p.id');

        $this->addSql('ALTER TABLE guest_invitation ADD CONSTRAINT FK_CC0531331FB8D185 FOREIGN KEY (host_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE login ADD CONSTRAINT FK_AA08CB10CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE profile_role ADD CONSTRAINT FK_E1A105FECCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D18157AA0F FOREIGN KEY (profile) REFERENCES profile (id)');
    }
}
