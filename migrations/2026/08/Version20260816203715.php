<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260816203715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add dex.banner_layers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD banner_layers TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP banner_layers');
    }
}
