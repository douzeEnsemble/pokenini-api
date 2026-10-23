<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261023200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add trainer_preference to keep the home page each trainer chose';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE trainer_preference (trainer_external_id VARCHAR(255) NOT NULL, home_page VARCHAR(16) NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_trainer_preference_trainer ON trainer_preference (trainer_external_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE trainer_preference');
    }
}
