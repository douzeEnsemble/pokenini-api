<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231229181510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Add pokemon's types";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon ADD primary_type_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE pokemon ADD secondary_type_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN pokemon.primary_type_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pokemon.secondary_type_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3ED5C8A7 FOREIGN KEY (primary_type_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3527A53 FOREIGN KEY (secondary_type_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_62DC90F3ED5C8A7 ON pokemon (primary_type_id)');
        $this->addSql('CREATE INDEX IDX_62DC90F3527A53 ON pokemon (secondary_type_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT FK_62DC90F3ED5C8A7');
        $this->addSql('ALTER TABLE pokemon DROP CONSTRAINT FK_62DC90F3527A53');
        $this->addSql('DROP INDEX IDX_62DC90F3ED5C8A7');
        $this->addSql('DROP INDEX IDX_62DC90F3527A53');
        $this->addSql('ALTER TABLE pokemon DROP primary_type_id');
        $this->addSql('ALTER TABLE pokemon DROP secondary_type_id');
    }
}
