<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261021160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add which shiny game: which_shiny_game and which_shiny_game_round';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE which_shiny_game (trainer_external_id VARCHAR(255) NOT NULL, status VARCHAR(16) NOT NULL, round_count INT NOT NULL, answered_count INT NOT NULL, correct_count INT NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_played_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ended_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, dex_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_which_shiny_game_trainer ON which_shiny_game (trainer_external_id)');
        $this->addSql('CREATE INDEX IDX_6DD0073C44FE8083 ON which_shiny_game (dex_id)');
        $this->addSql('CREATE TABLE which_shiny_game_round (position INT NOT NULL, shiny_slot INT NOT NULL, chosen_slot INT DEFAULT NULL, is_correct BOOLEAN DEFAULT NULL, answered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, which_shiny_game_id UUID NOT NULL, pokemon_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_which_shiny_game_round_position ON which_shiny_game_round (which_shiny_game_id, position)');
        $this->addSql('CREATE INDEX IDX_E815FC8482A29DBA ON which_shiny_game_round (which_shiny_game_id)');
        $this->addSql('CREATE INDEX IDX_E815FC842FE71C3E ON which_shiny_game_round (pokemon_id)');
        $this->addSql('ALTER TABLE which_shiny_game ADD CONSTRAINT FK_6DD0073C44FE8083 FOREIGN KEY (dex_id) REFERENCES dex (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE which_shiny_game_round ADD CONSTRAINT FK_E815FC8482A29DBA FOREIGN KEY (which_shiny_game_id) REFERENCES which_shiny_game (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE which_shiny_game_round ADD CONSTRAINT FK_E815FC842FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE which_shiny_game DROP CONSTRAINT FK_6DD0073C44FE8083');
        $this->addSql('ALTER TABLE which_shiny_game_round DROP CONSTRAINT FK_E815FC8482A29DBA');
        $this->addSql('ALTER TABLE which_shiny_game_round DROP CONSTRAINT FK_E815FC842FE71C3E');
        $this->addSql('DROP TABLE which_shiny_game');
        $this->addSql('DROP TABLE which_shiny_game_round');
    }
}
