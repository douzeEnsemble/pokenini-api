<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230323073053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename Messenger Action to Action Log';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE action_log (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, done_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, report_data VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN action_log.id IS \'(DC2Type:uuid)\'');
        $this->addSql('DROP TABLE messenger_action');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE messenger_action (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, done_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, report_data VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN messenger_action.id IS \'(DC2Type:uuid)\'');
        $this->addSql('DROP TABLE action_log');
    }
}
