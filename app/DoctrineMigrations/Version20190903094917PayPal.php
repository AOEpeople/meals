<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Adds "paymethod" and "orderId" columns to the transaction table (used for payment via PayPal)
 * Class Version20190903094917PayPal
 * @package Application\Migrations
 */
class Version20190903094917PayPal extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('ALTER TABLE transaction ADD paymethod VARCHAR(2048) DEFAULT NULL, ADD orderId VARCHAR(2048) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('ALTER TABLE transaction DROP paymethod, DROP orderId');
    }
}
