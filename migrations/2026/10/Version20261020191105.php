<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261020191105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add color game: pokemon_color_set, color_game, color_game_round and dex.can_be_color_play';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE color_game (trainer_external_id VARCHAR(255) NOT NULL, is_shiny BOOLEAN NOT NULL, status VARCHAR(16) NOT NULL, round_count INT NOT NULL, answered_count INT NOT NULL, correct_count INT NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_played_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ended_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, dex_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_color_game_trainer ON color_game (trainer_external_id)');
        $this->addSql('CREATE INDEX IDX_5EFC0AC844FE8083 ON color_game (dex_id)');
        $this->addSql('CREATE TABLE color_game_round (position INT NOT NULL, choices_order VARCHAR(255) NOT NULL, chosen_choice INT DEFAULT NULL, is_correct BOOLEAN DEFAULT NULL, answered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, color_game_id UUID NOT NULL, pokemon_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_color_game_round_position ON color_game_round (color_game_id, position)');
        $this->addSql('CREATE INDEX IDX_524F2875B719542B ON color_game_round (color_game_id)');
        $this->addSql('CREATE INDEX IDX_524F28752FE71C3E ON color_game_round (pokemon_id)');
        $this->addSql('CREATE TABLE pokemon_color_set (is_shiny BOOLEAN NOT NULL, position SMALLINT NOT NULL, codes VARCHAR(255) NOT NULL, names VARCHAR(255) NOT NULL, french_names VARCHAR(255) NOT NULL, id UUID NOT NULL, pokemon_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_pokemon_color_set_slot ON pokemon_color_set (pokemon_id, is_shiny, position)');
        $this->addSql('CREATE INDEX IDX_7583A752FE71C3E ON pokemon_color_set (pokemon_id)');
        $this->addSql('ALTER TABLE color_game ADD CONSTRAINT FK_5EFC0AC844FE8083 FOREIGN KEY (dex_id) REFERENCES dex (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE color_game_round ADD CONSTRAINT FK_524F2875B719542B FOREIGN KEY (color_game_id) REFERENCES color_game (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE color_game_round ADD CONSTRAINT FK_524F28752FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE pokemon_color_set ADD CONSTRAINT FK_7583A752FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE dex ADD can_be_color_play BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE color_game DROP CONSTRAINT FK_5EFC0AC844FE8083');
        $this->addSql('ALTER TABLE color_game_round DROP CONSTRAINT FK_524F2875B719542B');
        $this->addSql('ALTER TABLE color_game_round DROP CONSTRAINT FK_524F28752FE71C3E');
        $this->addSql('ALTER TABLE pokemon_color_set DROP CONSTRAINT FK_7583A752FE71C3E');
        $this->addSql('DROP TABLE color_game');
        $this->addSql('DROP TABLE color_game_round');
        $this->addSql('DROP TABLE pokemon_color_set');
        $this->addSql('ALTER TABLE dex DROP can_be_color_play');
    }
}
