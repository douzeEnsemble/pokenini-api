<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261021183000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link each pokemon to its origin generation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon ADD origin_generation_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F37C2DCE6C FOREIGN KEY (origin_generation_id) REFERENCES game_generation (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_62DC90F37C2DCE6C ON pokemon (origin_generation_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT FK_62DC90F37C2DCE6C');
        $this->addSql('DROP INDEX IDX_62DC90F37C2DCE6C');
        $this->addSql('ALTER TABLE pokemon DROP origin_generation_id');
    }
}
