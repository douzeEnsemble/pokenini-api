<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261108080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Keep the name of the types in every language in a single column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE type ADD names JSONB DEFAULT \'{}\' NOT NULL');
        $this->addSql('UPDATE type SET names = jsonb_build_object(\'en\', name, \'fr\', french_name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE type DROP names');
    }
}
