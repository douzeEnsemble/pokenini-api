<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240927070221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add collections';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE collection (id UUID NOT NULL, name VARCHAR(255) NOT NULL, french_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, order_number INT NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FC4D65325E237E06 ON collection (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FC4D6532989D9B62 ON collection (slug)');
        $this->addSql('DELETE FROM collection_availability');
        $this->addSql('ALTER TABLE collection_availability ADD collection_id UUID NOT NULL');
        $this->addSql('ALTER TABLE collection_availability DROP collection_slug');
        $this->addSql('ALTER TABLE collection_availability ADD CONSTRAINT FK_A594BB95514956FD FOREIGN KEY (collection_id) REFERENCES collection (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A594BB95514956FD ON collection_availability (collection_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE collection');
        $this->addSql('ALTER TABLE collection_availability DROP CONSTRAINT FK_A594BB95514956FD');
        $this->addSql('DROP INDEX IDX_A594BB95514956FD');
        $this->addSql('ALTER TABLE collection_availability ADD collection_slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE collection_availability DROP collection_id');
    }
}
