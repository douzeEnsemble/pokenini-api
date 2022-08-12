<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220812134053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD is_shiny BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE dex ALTER is_shiny DROP DEFAULT');

        $this->addSql("UPDATE dex SET is_shiny = true WHERE slug = 'homeshiny'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP is_shiny');
    }
}
