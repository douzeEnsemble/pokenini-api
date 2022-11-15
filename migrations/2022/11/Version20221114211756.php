<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221114211756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Add Trainer's dex feature";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE trainer_dex (id UUID NOT NULL, dex_id UUID DEFAULT NULL, trainer_token VARCHAR(255) NOT NULL, is_private BOOLEAN NOT NULL, is_on_home BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C93AEB5544FE8083 ON trainer_dex (dex_id)');
        $this->addSql('COMMENT ON COLUMN trainer_dex.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN trainer_dex.dex_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE trainer_dex ADD CONSTRAINT FK_C93AEB5544FE8083 FOREIGN KEY (dex_id) REFERENCES dex (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX trainers_dex ON trainer_dex (trainer_token, dex_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trainer_dex DROP CONSTRAINT FK_C93AEB5544FE8083');
        $this->addSql('DROP TABLE trainer_dex');
    }
}
