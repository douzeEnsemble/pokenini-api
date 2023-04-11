<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230413145230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update pokedex.trainer_dex_id from existing data';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
        UPDATE		pokedex AS p
        SET			trainer_dex_id = td.id
        FROM		trainer_dex AS td
        WHERE		p.dex_id = td.dex_id
                AND	td.trainer_external_id = p.trainer_external_id
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE pokedex SET trainer_dex_id = NULL');
    }
}
