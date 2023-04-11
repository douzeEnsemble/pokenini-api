<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230413090951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix required many to one';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex_availability ALTER pokemon_id SET NOT NULL');
        $this->addSql('ALTER TABLE dex_availability ALTER dex_id SET NOT NULL');
        $this->addSql('ALTER TABLE game ALTER bundle_id SET NOT NULL');
        $this->addSql('ALTER TABLE game_availability ALTER game_id SET NOT NULL');
        $this->addSql('ALTER TABLE game_bundle ALTER generation_id SET NOT NULL');
        $this->addSql('ALTER TABLE game_bundle_availability ALTER pokemon_id SET NOT NULL');
        $this->addSql('ALTER TABLE game_bundle_availability ALTER bundle_id SET NOT NULL');
        $this->addSql('ALTER TABLE pokedex ALTER dex_id SET NOT NULL');
        $this->addSql('ALTER TABLE pokedex ALTER pokemon_id SET NOT NULL');
        $this->addSql('ALTER TABLE pokemon ALTER original_game_bundle_id SET NOT NULL');
        $this->addSql('ALTER TABLE regional_dex_number ALTER region_id SET NOT NULL');
        $this->addSql('ALTER TABLE trainer_dex ALTER dex_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE regional_dex_number ALTER region_id DROP NOT NULL');
        $this->addSql('ALTER TABLE game_bundle_availability ALTER pokemon_id DROP NOT NULL');
        $this->addSql('ALTER TABLE game_bundle_availability ALTER bundle_id DROP NOT NULL');
        $this->addSql('ALTER TABLE game ALTER bundle_id DROP NOT NULL');
        $this->addSql('ALTER TABLE pokedex ALTER dex_id DROP NOT NULL');
        $this->addSql('ALTER TABLE pokedex ALTER pokemon_id DROP NOT NULL');
        $this->addSql('ALTER TABLE game_bundle ALTER generation_id DROP NOT NULL');
        $this->addSql('ALTER TABLE dex_availability ALTER pokemon_id DROP NOT NULL');
        $this->addSql('ALTER TABLE dex_availability ALTER dex_id DROP NOT NULL');
        $this->addSql('ALTER TABLE pokemon ALTER original_game_bundle_id DROP NOT NULL');
        $this->addSql('ALTER TABLE game_availability ALTER game_id DROP NOT NULL');
        $this->addSql('ALTER TABLE trainer_dex ALTER dex_id DROP NOT NULL');
    }
}
