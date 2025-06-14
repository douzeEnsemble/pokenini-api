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
        $this->addSql('ALTER TABLE category_form ADD french_name VARCHAR(255) NULL');
        $this->addSql('ALTER TABLE regional_form ADD french_name VARCHAR(255) NULL');
        $this->addSql('ALTER TABLE special_form ADD french_name VARCHAR(255) NULL');
        $this->addSql('ALTER TABLE variant_form ADD french_name VARCHAR(255) NULL');

        $this->addSql('UPDATE category_form SET french_name = name');
        $this->addSql('UPDATE regional_form SET french_name = name');
        $this->addSql('UPDATE special_form SET french_name = name');
        $this->addSql('UPDATE variant_form SET french_name = name');
        
        $this->addSql('ALTER TABLE category_form ALTER COLUMN french_name SET NOT NULL');
        $this->addSql('ALTER TABLE regional_form ALTER COLUMN french_name SET NOT NULL');
        $this->addSql('ALTER TABLE special_form ALTER COLUMN french_name SET NOT NULL');
        $this->addSql('ALTER TABLE variant_form ALTER COLUMN french_name SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category_form DROP french_name');
        $this->addSql('ALTER TABLE special_form DROP french_name');
        $this->addSql('ALTER TABLE regional_form DROP french_name');
        $this->addSql('ALTER TABLE variant_form DROP french_name');
    }
}
