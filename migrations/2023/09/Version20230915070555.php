<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230915070555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix action_log.error_trace length';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE action_log ALTER error_trace TYPE TEXT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE action_log ALTER error_trace TYPE VARCHAR(255)');
    }
}
