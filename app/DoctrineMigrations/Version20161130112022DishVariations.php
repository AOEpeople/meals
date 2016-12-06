<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Updates database with tables and constraints required to use dish variations.
 */
class Version20161130112022DishVariations extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('DROP TABLE dish_variation');
        $this->addSql('ALTER TABLE dish ADD parent_id INT DEFAULT NULL, ADD type VARCHAR(255) NOT NULL, CHANGE price price NUMERIC(10, 4) NOT NULL');
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB8727ACA70 FOREIGN KEY (parent_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_957D8CB8727ACA70 ON dish (parent_id)');
        $this->addSql('UPDATE dish SET type="dish"');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('CREATE TABLE dish_variation (id INT AUTO_INCREMENT NOT NULL, dish_id INT NOT NULL, description_de VARCHAR(4096) NOT NULL COLLATE utf8_unicode_ci, description_en VARCHAR(4096) NOT NULL COLLATE utf8_unicode_ci, enabled TINYINT(1) NOT NULL, INDEX IDX_B8E277BB148EB0CB (dish_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dish_variation ADD CONSTRAINT FK_B8E277BB148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB8727ACA70');
        $this->addSql('DROP INDEX IDX_957D8CB8727ACA70 ON dish');
        $this->addSql('ALTER TABLE dish DROP parent_id, DROP type, CHANGE price price NUMERIC(10, 4) DEFAULT NULL');
    }
}
