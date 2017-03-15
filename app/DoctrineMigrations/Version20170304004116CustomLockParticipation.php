<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Add a date field which holds the datetime when a
 * participant has to be registered to meal
 */
class Version20170304004116CustomLockParticipation extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('ALTER TABLE day ADD lockParticipationDateTime DATETIME DEFAULT NULL');
            // update empty fields lockParticipationDateTime with a date that lays 1 day before dateTime and is 16:00
        $this->addSql('UPDATE day set lockParticipationDateTime = DATE_ADD(DATE_SUB(DATE(dateTime),INTERVAL 1 DAY), INTERVAL 16 HOUR) where lockParticipationDateTime IS NULL');
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
