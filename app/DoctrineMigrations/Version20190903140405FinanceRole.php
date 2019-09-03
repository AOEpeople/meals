<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Adds/removes role for finance staff
 */
class Version20190903140405FinanceRole extends \Application\Migrations\AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('INSERT INTO role (id, title, sid) VALUES(5, \'Finance Staff\', \'ROLE_FINANCE\')');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('DELETE FROM role WHERE id IN (5)');
    }
}
