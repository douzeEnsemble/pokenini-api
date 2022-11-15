<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221113212114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add trainer token data on pokedex';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX pokemon_dex');
        $this->addSql("ALTER TABLE pokedex ADD trainer_external_id VARCHAR(255) NOT NULL DEFAULT 'f86cbe805674d85f7806b175b70647a6a9334631'");
        $this->addSql('ALTER TABLE pokedex ALTER trainer_external_id DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX pokemon_dex_trainer ON pokedex (pokemon_id, dex_id, trainer_external_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokedex DROP trainer_external_id');
        $this->addSql('DROP INDEX pokemon_dex_trainer');
        $this->addSql('CREATE UNIQUE INDEX pokemon_dex ON pokedex (pokemon_id, dex_id)');
    }
}
