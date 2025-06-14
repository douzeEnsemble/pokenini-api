<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250117143640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Dex.CanHoldElection';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD can_hold_election BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP can_hold_election');
    }
}
