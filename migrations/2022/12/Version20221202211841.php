<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221202211841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add descriptions and version to Dex';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE dex ADD description VARCHAR(655) NOT NULL DEFAULT ''");
        $this->addSql("ALTER TABLE dex ADD french_description VARCHAR(655) NOT NULL DEFAULT ''");
        $this->addSql('ALTER TABLE dex ADD version INT NOT NULL DEFAULT 1');

        $this->addSql('ALTER TABLE dex ALTER description DROP DEFAULT');
        $this->addSql('ALTER TABLE dex ALTER french_description DROP DEFAULT');
        $this->addSql('ALTER TABLE dex ALTER version DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP description');
        $this->addSql('ALTER TABLE dex DROP french_description');
        $this->addSql('ALTER TABLE dex DROP version');
    }
}
