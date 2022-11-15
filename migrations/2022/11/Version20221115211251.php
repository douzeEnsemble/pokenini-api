<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221115211251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Copy dex to trainer dex for my user';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
        INSERT INTO trainer_dex(id, dex_id, trainer_external_id, is_private, is_on_home)
        SELECT  gen_random_uuid(), id, 'f86cbe805674d85f7806b175b70647a6a9334631', is_private, is_private
        FROM    dex
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<SQL
        DELETE
        FROM trainer_dex
        WHERE trainer_external_id = 'f86cbe805674d85f7806b175b70647a6a9334631'
        SQL;
        
        $this->addSql($sql);
    }
}
