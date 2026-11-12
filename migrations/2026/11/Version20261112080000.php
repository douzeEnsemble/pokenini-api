<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261112080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the text color of the catch states, so the label written on their color stays readable';
    }

    public function up(Schema $schema): void
    {
        // Empty until the next "Catch state" sheet import fills it: the clients fall back to a default text color
        $this->addSql("ALTER TABLE catch_state ADD text_color VARCHAR(255) DEFAULT '' NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE catch_state DROP text_color');
    }
}
