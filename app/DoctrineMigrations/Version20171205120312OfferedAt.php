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
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema)
    {
        $table = $schema->getTable('participant');
        $table->addColumn('offeredAt', 'integer');
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema)
    {
        $schema->getTable('participant')->dropColumn('offeredAt');
    }
}