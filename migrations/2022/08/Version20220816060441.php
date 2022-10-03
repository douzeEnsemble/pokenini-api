<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220816060441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add simplified name and form label in english and french';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE pokemon ADD simplified_name VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addSql("ALTER TABLE pokemon ADD simplified_french_name VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addSql("ALTER TABLE pokemon ADD forms_label VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addSql("ALTER TABLE pokemon ADD forms_french_label VARCHAR(255) NOT NULL DEFAULT ''");

        $this->addSql('ALTER TABLE pokemon ALTER simplified_name DROP DEFAULT');
        $this->addSql('ALTER TABLE pokemon ALTER simplified_french_name DROP DEFAULT');
        $this->addSql('ALTER TABLE pokemon ALTER forms_label DROP DEFAULT');
        $this->addSql('ALTER TABLE pokemon ALTER forms_french_label DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon DROP simplified_name');
        $this->addSql('ALTER TABLE pokemon DROP simplified_french_name');
        $this->addSql('ALTER TABLE pokemon DROP forms_label');
        $this->addSql('ALTER TABLE pokemon DROP forms_french_label');
    }
}
