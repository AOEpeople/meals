<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version20171205120312OfferedAt
 * @package Application\Migrations
 */
class Version20171205120312OfferedAt extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('ALTER TABLE participant ADD offeredAt INT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('ALTER TABLE participant DROP offeredAt');
    }
}