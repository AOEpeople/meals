<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Updates database with tables and constraints required to store guest invitations.
 *
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class Version201611040904AddGuestInvitation extends AbstractMigration
{
	/**
	 * @param Schema $schema
	 */
	public function up(Schema $schema)
	{
		$this->validateDatabase();
		$this->updateSchema();
	}

	/**
	 * @param Schema $schema
	 */
	public function down(Schema $schema)
	{
		$this->validateDatabase();
		$this->addSql('DROP TABLE guest_invitation');
	}

	/**
	 * Create tables and constraints required to implement guest invitation.
	 */
	protected function updateSchema()
	{
		$this->addSql(
			'CREATE TABLE guest_invitation (' .
				'id VARCHAR(255) NOT NULL, ' .
				'host_id VARCHAR(255) NOT NULL, ' .
				'meal_day_id INT NOT NULL, ' .
				'created_on DATETIME NOT NULL, ' .
				'INDEX IDX_CC0531331FB8D185 (host_id), ' .
				'INDEX IDX_CC05313366C8FC2F (meal_day_id), ' .
				'PRIMARY KEY(id)' .
			') DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
		);
		$this->addSql(
			'ALTER TABLE guest_invitation ' .
			'ADD CONSTRAINT FK_CC0531331FB8D185 FOREIGN KEY (host_id) REFERENCES profile (id) ON DELETE NO ACTION'
		);
		$this->addSql(
			'ALTER TABLE guest_invitation ' .
			'ADD CONSTRAINT FK_CC05313366C8FC2F FOREIGN KEY (meal_day_id) REFERENCES day (id) ON DELETE NO ACTION'
		);
	}
}
