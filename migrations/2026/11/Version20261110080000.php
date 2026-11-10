<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261110080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Keep the names of the colours of the colour sets in every language in a single column';
    }

    public function up(Schema $schema): void
    {
        // Filled from the existing columns, which stay as the legacy shape of the API still reads them
        $this->addSql("ALTER TABLE pokemon_color_set ADD localized_names JSONB DEFAULT '{}' NOT NULL");
        $this->addSql("UPDATE pokemon_color_set SET localized_names = jsonb_build_object('en', names, 'fr', french_names)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon_color_set DROP localized_names');
    }
}
