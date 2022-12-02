<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221202211213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Up dex selection rule text length to avoid issues';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ALTER selection_rule TYPE VARCHAR(13570)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ALTER selection_rule TYPE VARCHAR(1357)');
    }
}
