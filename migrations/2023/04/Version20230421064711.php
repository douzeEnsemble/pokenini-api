<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230421064711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change the way version are handle';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD last_changed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        $this->addSql('UPDATE dex SET last_changed_at = NOW() WHERE last_changed_at IS NULL');
        $this->addSql('ALTER TABLE dex ALTER last_changed_at SET NOT NULL');
        $this->addSql('ALTER TABLE dex ALTER last_changed_at DROP DEFAULT');

        $this->addSql('ALTER TABLE dex DROP version');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD version INT NOT NULL');
        $this->addSql('ALTER TABLE dex DROP last_changed_at');
    }
}
