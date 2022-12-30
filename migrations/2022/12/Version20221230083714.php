<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221230083714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link Dex with Region';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD region_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE dex DROP region_name');
        $this->addSql('COMMENT ON COLUMN dex.region_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE dex ADD CONSTRAINT FK_F6CBDC0298260155 FOREIGN KEY (region_id) REFERENCES region (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F6CBDC0298260155 ON dex (region_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP CONSTRAINT FK_F6CBDC0298260155');
        $this->addSql('DROP INDEX IDX_F6CBDC0298260155');
        $this->addSql('ALTER TABLE dex ADD region_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE dex DROP region_id');
    }
}
