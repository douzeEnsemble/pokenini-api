<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261108130000 extends AbstractMigration
{
    private const array TABLES = ['category_form', 'regional_form', 'special_form', 'variant_form'];

    public function getDescription(): string
    {
        return 'Keep the name of the forms in every language in a single column';
    }

    public function up(Schema $schema): void
    {
        foreach (self::TABLES as $table) {
            $this->addSql("ALTER TABLE {$table} ADD names JSONB DEFAULT '{}' NOT NULL");
            $this->addSql("UPDATE {$table} SET names = jsonb_build_object('en', name, 'fr', french_name)");
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::TABLES as $table) {
            $this->addSql("ALTER TABLE {$table} DROP names");
        }
    }
}
