<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261021123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add shiny game: shiny_game and shiny_game_round';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE shiny_game (trainer_external_id VARCHAR(255) NOT NULL, status VARCHAR(16) NOT NULL, round_count INT NOT NULL, answered_count INT NOT NULL, correct_count INT NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_played_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ended_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, dex_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_shiny_game_trainer ON shiny_game (trainer_external_id)');
        $this->addSql('CREATE INDEX IDX_996313FA44FE8083 ON shiny_game (dex_id)');
        $this->addSql('CREATE TABLE shiny_game_round (position INT NOT NULL, is_shiny BOOLEAN NOT NULL, chosen_shiny BOOLEAN DEFAULT NULL, is_correct BOOLEAN DEFAULT NULL, answered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, shiny_game_id UUID NOT NULL, pokemon_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_shiny_game_round_position ON shiny_game_round (shiny_game_id, position)');
        $this->addSql('CREATE INDEX IDX_7CE0A176B4642DF ON shiny_game_round (shiny_game_id)');
        $this->addSql('CREATE INDEX IDX_7CE0A172FE71C3E ON shiny_game_round (pokemon_id)');
        $this->addSql('ALTER TABLE shiny_game ADD CONSTRAINT FK_996313FA44FE8083 FOREIGN KEY (dex_id) REFERENCES dex (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE shiny_game_round ADD CONSTRAINT FK_7CE0A176B4642DF FOREIGN KEY (shiny_game_id) REFERENCES shiny_game (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE shiny_game_round ADD CONSTRAINT FK_7CE0A172FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE shiny_game DROP CONSTRAINT FK_996313FA44FE8083');
        $this->addSql('ALTER TABLE shiny_game_round DROP CONSTRAINT FK_7CE0A176B4642DF');
        $this->addSql('ALTER TABLE shiny_game_round DROP CONSTRAINT FK_7CE0A172FE71C3E');
        $this->addSql('DROP TABLE shiny_game');
        $this->addSql('DROP TABLE shiny_game_round');
    }
}
