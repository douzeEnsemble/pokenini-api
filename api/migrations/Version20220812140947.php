<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220812140947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD is_private BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE dex ALTER is_private DROP DEFAULT');

        $this->addSql("UPDATE dex SET is_private = false WHERE slug LIKE 'home%'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP is_private');
    }
}
