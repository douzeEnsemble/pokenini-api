<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230409235932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add custom title and slug for dex. And multiple dex too';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX trainers_dex');
        $this->addSql('ALTER TABLE trainer_dex ADD name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE trainer_dex ADD french_name VARCHAR(255) DEFAULT NULL');
        $this->addSql("ALTER TABLE trainer_dex ADD slug VARCHAR(255) DEFAULT '' NOT NULL");
        $this->addSql('CREATE UNIQUE INDEX trainers_dex ON trainer_dex (trainer_external_id, dex_id, slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX trainers_dex');
        $this->addSql('ALTER TABLE trainer_dex DROP name');
        $this->addSql('ALTER TABLE trainer_dex DROP french_name');
        $this->addSql('ALTER TABLE trainer_dex DROP slug');
        $this->addSql('CREATE UNIQUE INDEX trainers_dex ON trainer_dex (trainer_external_id, dex_id)');
    }
}
