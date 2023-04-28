<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230427211256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add error log into Action log';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE action_log ADD error_trace VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE action_log DROP error_trace');
    }
}
