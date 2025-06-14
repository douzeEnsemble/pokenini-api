<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240922060002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add collection availabity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE collection_availability (pokemon_slug VARCHAR(255) NOT NULL, collection_slug VARCHAR(255) NOT NULL, availability VARCHAR(255) NOT NULL, id UUID NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE collection_availability');
    }
}
