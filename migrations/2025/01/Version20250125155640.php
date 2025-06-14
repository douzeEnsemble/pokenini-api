<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250125155640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove trainer_vote table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE trainer_vote');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE trainer_vote (trainer_external_id VARCHAR(255) NOT NULL, election_slug VARCHAR(255) NOT NULL, winners TEXT NOT NULL, losers TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, PRIMARY KEY(id))');
    }
}
