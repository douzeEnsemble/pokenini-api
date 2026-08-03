<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260803105617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE trainer_dex_link (trainer_external_id VARCHAR(255) NOT NULL, pair_id VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, source_trainer_dex_id UUID NOT NULL, target_trainer_dex_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_A9C06329D4C9B692 ON trainer_dex_link (source_trainer_dex_id)');
        $this->addSql('CREATE INDEX IDX_A9C06329CBCAD51E ON trainer_dex_link (target_trainer_dex_id)');
        $this->addSql('CREATE UNIQUE INDEX trainer_dex_link_edge ON trainer_dex_link (source_trainer_dex_id, target_trainer_dex_id)');
        $this->addSql('ALTER TABLE trainer_dex_link ADD CONSTRAINT FK_A9C06329D4C9B692 FOREIGN KEY (source_trainer_dex_id) REFERENCES trainer_dex (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE trainer_dex_link ADD CONSTRAINT FK_A9C06329CBCAD51E FOREIGN KEY (target_trainer_dex_id) REFERENCES trainer_dex (id) NOT DEFERRABLE');
        $this->addSql('DROP INDEX idx_75ea56e0fb7336f0');
        $this->addSql('DROP INDEX idx_75ea56e016ba31db');
        $this->addSql('DROP INDEX idx_75ea56e0e3bd61ce');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trainer_dex_link DROP CONSTRAINT FK_A9C06329D4C9B692');
        $this->addSql('ALTER TABLE trainer_dex_link DROP CONSTRAINT FK_A9C06329CBCAD51E');
        $this->addSql('DROP TABLE trainer_dex_link');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750');
        $this->addSql('CREATE INDEX idx_75ea56e0fb7336f0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX idx_75ea56e016ba31db ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0e3bd61ce ON messenger_messages (available_at)');
    }
}
