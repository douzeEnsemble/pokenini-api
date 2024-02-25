<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240225063840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Change the way pokemon's family links works";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT fk_62dc90f3c35e566a');
        $this->addSql('DROP INDEX idx_62dc90f3c35e566a');
        $this->addSql('ALTER TABLE pokemon ADD family VARCHAR(255) NULL');
        $this->addSql('UPDATE pokemon AS p SET family = pp.slug FROM pokemon AS pp WHERE p.family_id = pp.id');
        $this->addSql('UPDATE pokemon AS p SET family = slug WHERE family IS NULL');
        $this->addSql('ALTER TABLE pokemon ALTER COLUMN family SET NOT NULL');
        $this->addSql('ALTER TABLE pokemon DROP family_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon ADD family_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE pokemon DROP family');
        $this->addSql('COMMENT ON COLUMN pokemon.family_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT fk_62dc90f3c35e566a FOREIGN KEY (family_id) REFERENCES pokemon (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_62dc90f3c35e566a ON pokemon (family_id)');
    }
}
