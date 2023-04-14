<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230414123828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Trainer Dex values are mandatory now';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trainer_dex ALTER name SET NOT NULL');
        $this->addSql('ALTER TABLE trainer_dex ALTER french_name SET NOT NULL');
        $this->addSql('ALTER TABLE trainer_dex ALTER slug DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trainer_dex ALTER name DROP NOT NULL');
        $this->addSql('ALTER TABLE trainer_dex ALTER french_name DROP NOT NULL');
        $this->addSql('ALTER TABLE trainer_dex ALTER slug SET DEFAULT \'\'');
    }
}
