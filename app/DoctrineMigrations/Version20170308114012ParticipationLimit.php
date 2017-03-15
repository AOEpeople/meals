<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Add the participation_limit field to meal entity,
 * to be able to set a participation limit for each meal if necessary.
 */
class Version20170308114012ParticipationLimit extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('ALTER TABLE meal ADD participation_limit INT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('ALTER TABLE meal DROP participation_limit');
    }
}
