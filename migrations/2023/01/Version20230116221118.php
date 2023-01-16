<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230116221118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Regional Dex Number link with Region';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE regional_dex_number ADD region_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE regional_dex_number DROP region_name');
        $this->addSql('COMMENT ON COLUMN regional_dex_number.region_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE regional_dex_number ADD CONSTRAINT FK_6507F50998260155 FOREIGN KEY (region_id) REFERENCES region (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6507F50998260155 ON regional_dex_number (region_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE regional_dex_number DROP CONSTRAINT FK_6507F50998260155');
        $this->addSql('DROP INDEX IDX_6507F50998260155');
        $this->addSql('ALTER TABLE regional_dex_number ADD region_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE regional_dex_number DROP region_id');
    }
}
