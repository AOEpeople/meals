<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Adds "settlementHash" column to profile table (used to settle accounts)
 * Class Version20190903095214SettlementHash
 * @package Application\Migrations
 */
class Version20190903095214SettlementHash extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('ALTER TABLE profile ADD settlementHash VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('ALTER TABLE profile DROP settlementHash');
    }
}