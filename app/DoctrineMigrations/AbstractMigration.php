<?php

namespace Application\Migrations;


use Doctrine\DBAL\Migrations\AbstractMigration as DoctrineAbstractMigration;


/**
 * Base migration class.
 *
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
abstract class AbstractMigration extends DoctrineAbstractMigration
{
    /**
     * Checks if the migrations are executed on the supported database server.
     */
    protected function validateDatabase()
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );
    }
}
