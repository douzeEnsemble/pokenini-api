<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221002080634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a color to Catch State';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE catch_state ADD color VARCHAR(255) NOT NULL DEFAULT ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE catch_state DROP color');
    }
}
