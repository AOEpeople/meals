<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to generate application database schema.
 */
final class Version20210820051842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Generate application schema';
    }

    public function up(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        $this->addSql('CREATE TABLE Category (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(128) NOT NULL, title_en VARCHAR(255) NOT NULL, title_de VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_FF3A7B97989D9B62 (slug), PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('CREATE TABLE day (id INT AUTO_INCREMENT NOT NULL, week_id INT DEFAULT NULL, enabled TINYINT(1) NOT NULL, message VARCHAR(255) DEFAULT NULL, dateTime DATETIME NOT NULL, lockParticipationDateTime DATETIME DEFAULT NULL, INDEX IDX_E5A02990C86F3B2F (week_id), PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dish (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, title_en VARCHAR(255) NOT NULL, description_en LONGTEXT DEFAULT NULL, title_de VARCHAR(255) NOT NULL, description_de LONGTEXT DEFAULT NULL, price NUMERIC(10, 4) NOT NULL, enabled TINYINT(1) NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_957D8CB8989D9B62 (slug), INDEX IDX_957D8CB8727ACA70 (parent_id), INDEX IDX_957D8CB812469DE2 (category_id), PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guest_invitation (id VARCHAR(255) NOT NULL, host_id VARCHAR(255) NOT NULL, meal_day_id INT NOT NULL, created_on DATETIME NOT NULL, INDEX IDX_CC0531331FB8D185 (host_id), INDEX IDX_CC05313366C8FC2F (meal_day_id), PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('CREATE TABLE login (id VARCHAR(255) NOT NULL, profile_id VARCHAR(255) DEFAULT NULL, password VARCHAR(128) NOT NULL, UNIQUE INDEX UNIQ_AA08CB10CCFA12B8 (profile_id), PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meal (id INT AUTO_INCREMENT NOT NULL, dish_id INT DEFAULT NULL, day INT DEFAULT NULL, dateTime DATETIME NOT NULL, price NUMERIC(10, 4) NOT NULL, participation_limit INT NOT NULL, INDEX IDX_9EF68E9C148EB0CB (dish_id), INDEX IDX_9EF68E9CE5A02990 (day), PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant (id INT AUTO_INCREMENT NOT NULL, meal_id INT DEFAULT NULL, profile_id VARCHAR(255) DEFAULT NULL, comment VARCHAR(2048) DEFAULT NULL, guestName VARCHAR(255) DEFAULT NULL, costAbsorbed TINYINT(1) DEFAULT \'0\' NOT NULL, confirmed TINYINT(1) NOT NULL, offeredAt INT NOT NULL, INDEX IDX_D79F6B11639666D6 (meal_id), INDEX IDX_D79F6B11CCFA12B8 (profile_id), PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile (id VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, firstName VARCHAR(255) DEFAULT NULL, company VARCHAR(255) DEFAULT NULL, settlementHash VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile_role (profile_id VARCHAR(255) NOT NULL, role_id INT NOT NULL, INDEX IDX_E1A105FED60322AC (role_id), INDEX IDX_E1A105FECCFA12B8 (profile_id), PRIMARY KEY(profile_id, role_id)) ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, sid VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_57698A6A57167AB4 (sid), PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session (sess_id VARBINARY(128) NOT NULL, sess_data BLOB NOT NULL, sess_lifetime INT UNSIGNED NOT NULL, sess_time INT UNSIGNED NOT NULL, PRIMARY KEY(sess_id), INDEX `session_sess_lifetime_idx` (`sess_lifetime`)) ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, profile VARCHAR(255) NOT NULL, date DATETIME NOT NULL, amount NUMERIC(10, 4) NOT NULL, paymethod VARCHAR(2048) DEFAULT NULL, orderId VARCHAR(24) DEFAULT NULL, UNIQUE INDEX UNIQ_723705D1FA237437 (orderId), INDEX IDX_723705D18157AA0F (profile), PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('CREATE TABLE week (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) NOT NULL, message VARCHAR(255) DEFAULT NULL, year SMALLINT NOT NULL, calendarWeek SMALLINT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB');

        $this->addSql('INSERT INTO role (title, sid) SELECT \'Kitchen Staff\', \'ROLE_KITCHEN_STAFF\' WHERE NOT EXISTS (SELECT 1 FROM role WHERE sid = \'ROLE_KITCHEN_STAFF\')');
        $this->addSql('INSERT INTO role (title, sid) SELECT \'User\', \'ROLE_USER\' WHERE NOT EXISTS (SELECT 1 FROM role WHERE sid = \'ROLE_USER\')');
        $this->addSql('INSERT INTO role (title, sid) SELECT \'Guest\', \'ROLE_GUEST\' WHERE NOT EXISTS (SELECT 1 FROM role WHERE sid = \'ROLE_GUEST\')');
        $this->addSql('INSERT INTO role (title, sid) SELECT \'Administrator\', \'ROLE_ADMIN\' WHERE NOT EXISTS (SELECT 1 FROM role WHERE sid = \'ROLE_ADMIN\')');
        $this->addSql('INSERT INTO role (title, sid) SELECT \'Finance Staff\', \'ROLE_FINANCE\' WHERE NOT EXISTS (SELECT 1 FROM role WHERE sid = \'ROLE_FINANCE\')');
    }

    public function down(Schema $schema): void
    {
        $this->abortOnIncompatibleDB();

        $this->addSql('DROP TABLE Category');
        $this->addSql('DROP TABLE day');
        $this->addSql('DROP TABLE dish');
        $this->addSql('DROP TABLE guest_invitation');
        $this->addSql('DROP TABLE login');
        $this->addSql('DROP TABLE meal');
        $this->addSql('DROP TABLE participant');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE profile_role');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE week');
    }

    /**
     * @throws Exception
     */
    function abortOnIncompatibleDB(): void
    {
        $currPlatform = $this->connection->getDatabasePlatform();
        $this->abortIf(
            !($currPlatform instanceof MySQLPlatform || $currPlatform instanceof MariaDBPlatform),
            'Migration can only be executed safely on \'mysql\' or \'maria-db\'.'
        );
    }
}
