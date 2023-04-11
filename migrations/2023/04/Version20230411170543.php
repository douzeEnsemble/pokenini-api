<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230411170543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Pokédex link with TrainerDex instead of Dex and Trainer id, part 1';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokedex ADD trainer_dex_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN pokedex.trainer_dex_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE pokedex ADD CONSTRAINT FK_6336F6A777B0DA37 FOREIGN KEY (trainer_dex_id) REFERENCES trainer_dex (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6336F6A777B0DA37 ON pokedex (trainer_dex_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokedex DROP CONSTRAINT FK_6336F6A777B0DA37');
        $this->addSql('DROP INDEX IDX_6336F6A777B0DA37');
        $this->addSql('ALTER TABLE pokedex DROP trainer_dex_id');
    }
}
