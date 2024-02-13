<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240213081311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a calculated table `pokemon_availabilities`';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pokemon_availabilities (id UUID NOT NULL, pokemon_id UUID NOT NULL, category VARCHAR(255) NOT NULL, items TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_40D1D68A2FE71C3E ON pokemon_availabilities (pokemon_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_40D1D68A2FE71C3E64C19C1 ON pokemon_availabilities (pokemon_id, category)');
        $this->addSql('COMMENT ON COLUMN pokemon_availabilities.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokemon_availabilities.pokemon_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokemon_availabilities.items IS \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE pokemon_availabilities ADD CONSTRAINT FK_40D1D68A2FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon_availabilities DROP CONSTRAINT FK_40D1D68A2FE71C3E');
        $this->addSql('DROP TABLE pokemon_availabilities');
    }
}
