<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230416073019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove pokedex useless fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokedex DROP dex_id');
        $this->addSql('ALTER TABLE pokedex DROP trainer_external_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokedex ADD dex_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE pokedex ADD trainer_external_id VARCHAR(255) DEFAULT NULL');
    }
}
