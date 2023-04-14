<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230414132731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change Trainer Dex unique key';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX trainers_dex');
        $this->addSql('CREATE UNIQUE INDEX trainers_dex ON trainer_dex (trainer_external_id, slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX trainers_dex');
        $this->addSql('CREATE UNIQUE INDEX trainers_dex ON trainer_dex (trainer_external_id, dex_id, slug)');
    }
}
