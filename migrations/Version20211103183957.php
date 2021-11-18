<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211103183957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a hidden flag to the user profile.';
    }

    public function up(Schema $schema): void
    {
        // Add the hidden flag from user profile
        $this->addSql('ALTER TABLE profile ADD hidden TINYINT(1) NOT NULL DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        // Drop the hidden flag from user profile
        $this->addSql('ALTER TABLE profile DROP hidden');
    }
}
