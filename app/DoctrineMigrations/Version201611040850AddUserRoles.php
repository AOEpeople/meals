<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Adds/removes database schema with initial data required to implement user roles.
 *
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class Version201611040850AddUserRoles extends AbstractMigration
{
	/**
	 * @param Schema $schema
	 */
	public function up(Schema $schema)
	{
		$this->validateDatabase();

		$this->updateSchema();
		$this->addRoles();
	}

	/**
	 * @param Schema $schema
	 */
	public function down(Schema $schema)
	{
		$this->validateDatabase();

		$this->deleteRoles();
		$this->rollbackSchema();
	}

	/**
	 * Creates all tables and constraints required to implement user roles.
	 */
	protected function updateSchema()
	{
		$this->addSql(
			'CREATE TABLE role (' .
			'   id INT AUTO_INCREMENT NOT NULL,' .
			'   name VARCHAR(255) NOT NULL,' .
			'   PRIMARY KEY(id) ' .
			') DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
		);
		$this->addSql(
			'CREATE TABLE profile_role (' .
			'   profile_id VARCHAR(255) NOT NULL,' .
			'   role_id INT NOT NULL,' .
			'   INDEX IDX_E1A105FECCFA12B8 (profile_id),' .
			'   INDEX IDX_E1A105FED60322AC (role_id),' .
			'   PRIMARY KEY(profile_id, role_id) ' .
			') DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
		);
		$this->addSql(
			'ALTER TABLE profile_role ' .
			'ADD CONSTRAINT FK_E1A105FECCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE'
		);
		$this->addSql(
			'ALTER TABLE profile_role ' .
			'ADD CONSTRAINT FK_E1A105FED60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE'
		);
	}

	/**
	 * Remove all tables and constraints created to implement user roles.
	 */
	protected function rollbackSchema()
	{
		$this->addSql('ALTER TABLE profile_role DROP FOREIGN KEY FK_E1A105FED60322AC');
		$this->addSql('DROP TABLE profile_role');
		$this->addSql('DROP TABLE role');
	}

	/**
	 * Adds new user roles.
	 */
	protected function addRoles()
	{
		$this->addSql('INSERT INTO role VALUES(1, \'Kitchen Staff\')');
		$this->addSql('INSERT INTO role VALUES(2, \'User\')');
		$this->addSql('INSERT INTO role VALUES(3, \'Guest\')');
		$this->addSql('INSERT INTO role VALUES(4, \'Administrator\')');
	}

	/**
	 * Deletes previously added user roles.
	 */
	protected function deleteRoles()
	{
		$this->addSql('DELETE FROM role WHERE id IN (1, 2, 3, 4)');
	}
}
