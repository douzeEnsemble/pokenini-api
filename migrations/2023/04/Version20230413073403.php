<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230413073403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set default value for trainer dex is_private and is_on_home fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trainer_dex ALTER is_private SET DEFAULT true');
        $this->addSql('ALTER TABLE trainer_dex ALTER is_on_home SET DEFAULT false');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trainer_dex ALTER is_private DROP DEFAULT');
        $this->addSql('ALTER TABLE trainer_dex ALTER is_on_home DROP DEFAULT');
    }
}
