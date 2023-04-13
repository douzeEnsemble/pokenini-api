<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230413145753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change Pokedex from Dex/TrainerExternal fro TrainerDex. '.
            'Fields are not removed to not loose data at first.'.
            'Will be deleted after';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokedex DROP CONSTRAINT fk_6336f6a744fe8083');
        $this->addSql('DROP INDEX idx_6336f6a744fe8083');
        $this->addSql('DROP INDEX pokemon_dex_trainer');
        $this->addSql('CREATE UNIQUE INDEX pokemon_dex_trainer ON pokedex (pokemon_id, trainer_dex_id)');
        $this->addSql('ALTER TABLE pokedex ALTER dex_id DROP NOT NULL');
        $this->addSql('ALTER TABLE pokedex ALTER trainer_external_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX pokemon_dex_trainer');
        $this->addSql('COMMENT ON COLUMN pokedex.dex_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE pokedex ADD CONSTRAINT fk_6336f6a744fe8083 FOREIGN KEY (dex_id) REFERENCES dex (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_6336f6a744fe8083 ON pokedex (dex_id)');
        $this->addSql('CREATE UNIQUE INDEX pokemon_dex_trainer ON pokedex (pokemon_id, dex_id, trainer_external_id)');
        $this->addSql('ALTER TABLE pokedex ALTER dex_id SET NOT NULL');
        $this->addSql('ALTER TABLE pokedex ALTER trainer_external_id SET NOT NULL');
    }
}
