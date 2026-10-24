<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261024080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Group the dex by category';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE dex_category (name VARCHAR(255) NOT NULL, french_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A1926015E237E06 ON dex_category (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A192601989D9B62 ON dex_category (slug)');
        $this->addSql('ALTER TABLE dex ADD category_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE dex ADD CONSTRAINT FK_F6CBDC0212469DE2 FOREIGN KEY (category_id) REFERENCES dex_category (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_F6CBDC0212469DE2 ON dex (category_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP CONSTRAINT FK_F6CBDC0212469DE2');
        $this->addSql('DROP INDEX IDX_F6CBDC0212469DE2');
        $this->addSql('ALTER TABLE dex DROP category_id');
        $this->addSql('DROP TABLE dex_category');
    }
}
