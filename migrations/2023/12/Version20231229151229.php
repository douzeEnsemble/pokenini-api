<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231229151229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Adding type (pokemon's type)";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE type (id UUID NOT NULL, name VARCHAR(255) NOT NULL, french_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, color VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8CDE57295E237E06 ON type (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8CDE5729989D9B62 ON type (slug)');
        $this->addSql('COMMENT ON COLUMN type.id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE type');
    }
}
