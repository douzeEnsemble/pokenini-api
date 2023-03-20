<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230320155256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add messenger action';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE messenger_action (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, done_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, report_data VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN messenger_action.id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE messenger_action');
    }
}
