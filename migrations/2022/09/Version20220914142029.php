<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220914142029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add deleted_at on entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE catch_state ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE category_form ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dex ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE dex ALTER is_display_form DROP DEFAULT');
        $this->addSql('ALTER TABLE game ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE game_bundle ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE game_generation ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE regional_form ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE special_form ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE variant_form ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE variant_form DROP deleted_at');
        $this->addSql('ALTER TABLE catch_state DROP deleted_at');
        $this->addSql('ALTER TABLE regional_form DROP deleted_at');
        $this->addSql('ALTER TABLE dex DROP deleted_at');
        $this->addSql('ALTER TABLE dex ALTER is_display_form SET DEFAULT true');
        $this->addSql('ALTER TABLE special_form DROP deleted_at');
        $this->addSql('ALTER TABLE game DROP deleted_at');
        $this->addSql('ALTER TABLE category_form DROP deleted_at');
        $this->addSql('ALTER TABLE game_generation DROP deleted_at');
        $this->addSql('ALTER TABLE game_bundle DROP deleted_at');
    }
}
