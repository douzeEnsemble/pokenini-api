<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221125192813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Regional DexNumber';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE regional_dex_number (id UUID NOT NULL, pokemon_name VARCHAR(255) NOT NULL, region_name VARCHAR(255) NOT NULL, dex_number INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN regional_dex_number.id IS \'(DC2Type:uuid)\'');

        $this->addSql('ALTER TABLE dex ADD region_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE regional_dex_number');

        $this->addSql('ALTER TABLE dex DROP region_name');
    }
}
