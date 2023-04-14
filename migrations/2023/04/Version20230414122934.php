<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230414122934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Copy dex default values into trainer dex values';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
        UPDATE		trainer_dex AS td
        SET			name = d.name,
                    french_name = d.french_name,
                    slug = d.slug
        FROM		dex AS d
        WHERE		d.id = td.dex_id
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<SQL
        UPDATE		trainer_dex AS td
        SET			name = NULL,
                    french_name = NULL,
                    slug = ''
        SQL;

        $this->addSql($sql);
    }
}
