<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Update future and existing meal price from 3.20 to 3.40.
 */
class Version20180103143233MealPrice extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('UPDATE dish SET price = 3.4000 WHERE price = 3.2000');
        $this->addSql("UPDATE meal SET price = 3.4000 WHERE DATE(dateTime) > '2018-01-01' AND price = 3.2000");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->validateDatabase();
        $this->addSql('UPDATE dish SET price = 3.2000 WHERE price = 3.4000');
        $this->addSql("UPDATE meal SET price = 3.2000 WHERE DATE(dateTime) > '2018-01-01' AND price = 3.4000");
    }
}
