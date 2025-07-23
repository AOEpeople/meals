<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240718094546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dish ADD diet VARCHAR(255) NOT NULL, CHANGE one_serving_size one_serving_size TINYINT(1) NOT NULL');
        $this->addSql('update dish set diet="meat" where 1=1');
        $this->addSql('ALTER TABLE day ADD CONSTRAINT FK_E5A02990C86F3B2F FOREIGN KEY (week_id) REFERENCES week (id)');
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB812469DE2 FOREIGN KEY (category_id) REFERENCES Category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB8727ACA70 FOREIGN KEY (parent_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guest_invitation ADD CONSTRAINT FK_CC05313366C8FC2F FOREIGN KEY (meal_day_id) REFERENCES day (id) ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE meal ADD CONSTRAINT FK_9EF68E9C148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id)');
        $this->addSql('ALTER TABLE meal ADD CONSTRAINT FK_9EF68E9CE5A02990 FOREIGN KEY (day) REFERENCES day (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11639666D6 FOREIGN KEY (meal_id) REFERENCES meal (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B1159E5119C FOREIGN KEY (slot_id) REFERENCES slot (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B1159E5119C ON participant (slot_id)');
        $this->addSql('ALTER TABLE profile_role ADD CONSTRAINT FK_E1A105FED60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dish DROP COLUMN diet, CHANGE one_serving_size one_serving_size TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11639666D6');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B1159E5119C');
        $this->addSql('DROP INDEX IDX_D79F6B1159E5119C ON participant');
        $this->addSql('ALTER TABLE profile_role DROP FOREIGN KEY FK_E1A105FED60322AC');
        $this->addSql('ALTER TABLE meal DROP FOREIGN KEY FK_9EF68E9C148EB0CB');
        $this->addSql('ALTER TABLE meal DROP FOREIGN KEY FK_9EF68E9CE5A02990');
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB812469DE2');
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB8727ACA70');
        $this->addSql('ALTER TABLE day DROP FOREIGN KEY FK_E5A02990C86F3B2F');
        $this->addSql('ALTER TABLE guest_invitation DROP FOREIGN KEY FK_CC05313366C8FC2F');
    }

    /**
     * @throws Exception
     */
    function abortOnIncompatibleDB(): void
    {
        $currPlatform = $this->connection->getDatabasePlatform();
        $this->abortIf(
            !($currPlatform instanceof MySQLPlatform || $currPlatform instanceof MariaDBPlatform),
            'Migration can only be executed safely on \'mysql\' or \'maria-db\'.'
        );
    }
}
