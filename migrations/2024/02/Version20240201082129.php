<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240201082129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add french name for forms';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category_form ADD french_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE regional_form ADD french_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE special_form ADD french_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE variant_form ADD french_name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE variant_form DROP french_name');
        $this->addSql('ALTER TABLE category_form DROP french_name');
        $this->addSql('ALTER TABLE special_form DROP french_name');
        $this->addSql('ALTER TABLE regional_form DROP french_name');
    }
}
