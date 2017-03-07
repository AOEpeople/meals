<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170304004116AdaptDayEntity extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('ALTER TABLE day ADD lockParticipationDateTime DATETIME DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('ALTER TABLE day DROP lockParticipationDateTime');
    }
}
