<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261107200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Choose the dex shown on the hub';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trainer_dex ADD is_on_hub BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trainer_dex DROP is_on_hub');
    }
}
